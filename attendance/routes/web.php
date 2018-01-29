<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router -> get('/api/attendance/{ev}', function(Request $request, $ev = 0) use ($router) {
	//Declare variables.
	$dataSet = [];

	//Get the vars.
	$ev = filter_var($ev, FILTER_SANITIZE_NUMBER_INT);

	//Get the event.
	$events = app('db') -> select('SELECT * FROM event WHERE id = ?', [$ev]);
	$event = (!isset($events[0])) ? null : $events[0];

	//If there is no event.
	if (!$event) {
		return response("There was no event found to take attendance.", 400);
	} else {
		//Get people and family information.
		$people = app('db') -> select('SELECT p.id AS id, p.name as name, f.name as family '
				. 'FROM people AS p '
				. 'LEFT JOIN family AS f ON f.id = p.family ORDER BY family, name ASC');

		//Get attendance.
		$attendance = app('db') -> select('SELECT * FROM attendance WHERE event = ?', [$ev]);

		//For each person.
		foreach($people as $person) {
			//Set the person's attendance to false.
			$person -> attended = false;

			foreach($attendance as $attended) {
				//Check if they've attended.
				if ($attended -> person === $person -> id) {
					//Set the attendance.
					$person -> attended = true;

					//Break out of the loop.
					break;
				}
			}

			$dataSet[(!$person -> family) ? 'Unsorted' : $person -> family][] = $person;
		}

		//Return data.
		return response() -> json(['data' => $dataSet]);
	}
});

$router -> put('/api/attendance/{event}/{person}', function(Request $request, $event = 0, $person = 0) use ($router) {
	//Get the vars.
	$event = filter_var($event, FILTER_SANITIZE_NUMBER_INT);
	$person = filter_var($person, FILTER_SANITIZE_NUMBER_INT);

	//If the person is present.
	if (count(app('db') -> select('SELECT * FROM attendance WHERE event = ? AND person = ?', [$event, $person])) > 0) {
		//They are now absent.
		app('db') -> delete('DELETE FROM attendance WHERE event = ? AND person = ?', [$event, $person]);

		//Return data.
		return response() -> json(['data' => 0]);
	} else {
		//They are now present.
		app('db') -> insert('INSERT INTO attendance (event, person) VALUES (?, ?)', [$event, $person]);

		//Return data.
		return response() -> json(['data' => 1]);
	}
});

$router -> get('/api/event', function(Request $request) use ($router) {
	return response() -> json(['data' => app('db') -> select('SELECT * FROM event ORDER BY date DESC')]);
});

$router -> post('/api/event', function(Request $request) use ($router) {
	//Validation.
	if (!$request -> name) {
		return response("You must provide the event's name.", 400);
	} elseif (!$request -> date) {
		return response("You must provide the event's date.", 400);
	} else {
		//Get the post vars.
		$name = filter_var($request -> name, FILTER_SANITIZE_STRING);
		$date = strtotime(filter_var($request -> date, FILTER_SANITIZE_STRING));

		//If there are existing events.
		if (count(app('db') -> select('SELECT * FROM event WHERE name = ? AND date = ?', [$name, $date])) > 0) {
			return response("An event already exists with that name and date.", 400);
		} else {
			//If the system fails to submit the person.
			if (!app('db') -> insert("INSERT INTO event (name, date) VALUES (?, ?)", [$name, $date])) {
				return response("Failed to add this event.", 500);
			} else {
				return response() -> json(['message' => 'This event was added.']);
			}
		}
	}
});

$router -> get('/api/family/{id}', function(Request $request, $id = 0) use ($router) {
	$id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
	return response() -> json(['data' => app('db') -> select('SELECT * FROM family WHERE id = ?', [$id])]);
});

$router -> get('/api/family', function(Request $request) use ($router) {
	return response() -> json(['data' => app('db') -> select('SELECT * FROM family ORDER BY name ASC')]);
});

$router -> post('/api/family', function(Request $request) use ($router) {
	//If there is no name.
	if (!$request -> name) {
		return response("You must provide the family's name.", 400);
	} else {
		//Get the post vars.
		$name = filter_var($request -> name, FILTER_SANITIZE_STRING);

		//If there are existing people with that name.
		if (count(app('db') -> select('SELECT * FROM family WHERE name = ?', [$name])) > 0) {
			return response("A family already exists with this name, try including a name hyphenation.", 400);
		} else {
			//If the system fails to submit the person.
			if (!app('db') -> insert("INSERT INTO family (name) VALUES (?)", [$name])) {
				return response("Failed to add this family.", 500);
			} else {
				return response() -> json(['message' => 'This family was added.']);
			}
		}
	}
});

$router -> get('/api/people/{id}', function(Request $request, $id = 0) use ($router) {
	$id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
	return response() -> json(['data' => app('db') -> select('SELECT * FROM person WHERE id = ?', [$id])]);
});

$router -> get('/api/people', function(Request $request) use ($router) {
	return response() -> json(['data' => app('db') -> select('SELECT * FROM person ORDER BY name ASC')]);
});

$router -> post('/api/people', function(Request $request) use ($router) {
	//If there is no name.
	if (!$request -> name) {
		return response("You must provide the person's name.", 400);
	} else {
		//Get the post vars.
		$name = filter_var($request -> name, FILTER_SANITIZE_STRING);
		$family = filter_var($request -> family, FILTER_SANITIZE_NUMBER_INT);

		//If there are existing people with that name.
		if (count(app('db') -> select('SELECT * FROM people WHERE name = ?', [$name])) > 0) {
			return response("A person already exists with that name, try including a nickname in paranthesis.", 400);
		} else {
			//If the system fails to submit the person.
			if (!app('db') -> insert("INSERT INTO people (name, family) VALUES (?, ?)", [$name, $family])) {
				return response("Failed to add this person.", 500);
			} else {
				return response() -> json(['message' => 'This person was added.']);
			}
		}
	}
});

$router -> get('/', function(Request $request) use ($router) {
	//Get the current event.
	$event	=	app('db') -> select('SELECT * FROM event WHERE date <= ? ORDER BY date DESC LIMIT 1', [time()]);

	//Return the view.
	return view('web', [
		'path' => $request -> url(),
		'event' => (!isset($event[0])) ? null : $event[0],
		'families' => array()
	]);
});


