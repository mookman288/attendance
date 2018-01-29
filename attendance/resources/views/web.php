<!DOCTYPE html>
<html data-path="<?php print($path); ?>">
	<head>
		<title>Attendance</title>
		<meta charset="utf-8" />
		<meta name=viewport content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.6.2/css/bulma.min.css" />
		<style type="text/css">
			/* https://stephanwagner.me/only-css-loading-spinner */

			@keyframes loading {
				to { transform: rotate(360deg); }
			}

			.loading {
				background-color: rgba(255, 255, 255, 0.5);
			}

			.loading:before {
				content: '';
				box-sizing: border-box;
				position: absolute;
				top: 50%;
				left: 50%;
				width: 64px;
				height: 64px;
				margin-top: -32px;
				margin-left: -32px;
				border-radius: 50%;
				border-top: 2px solid #00d1b2;
				border-right: 2px solid transparent;
				animation: loading .6s linear infinite;
			}
	</style>
	</head>
	<body>
		<main>
			<header class="hero is-small is-primary">
				<nav class="navbar is-fixed hero is-primary">
					<ul class="navbar-brand">
						<li class="navbar-item"><h1>Attendance</h1></li>
						<li class="navbar-item"><button class="load button is-warning" data-load="add-event">
							Add Event
						</button></li>
						<li class="navbar-item"><button class="load button is-info" data-load="add-family">
							Add Family
						</button></li>
						<li class="navbar-item"><button class="load button is-link" data-load="add-people">
							Add Person
						</button></li>
					</ul>
				</nav>
			</header>
			<section class="section">
				<form method="get" action="<?php printf("%s/api/event", $path); ?>" class="update is-pulled-right"
					data-id="id" data-label="date, name">
					<div class="field">
						<div class="control">
							<div class="select">
								<select id="event" class="input">
									<option value="">Choose Event</option>
								</select>
							</div>
						</div>
					</div>
				</form>
				<h2 class="subtitle">
					Current Event:
					<?php if (!$event) { ?>
						None
					<?php } else { ?>
					<?php printf("%s (%s)", $event -> name, date('Y-m-d', $event -> date)); ?>
					<?php } ?>
				</h2>
				<div>
					<h3 class="subtitle">Take Attendance:</h3>
					<div class="attendance" data-event="<?php if ($event) print($event -> id); ?>"></div>
				</div>
			</section>
		</main>
		<div id="add-event" class="hero is-fullheight is-white is-invisible">
			<div class="section">
				<button class="delete button is-danger is-pulled-right">Close</button>
				<h2 class="subtitle">Add Event</h2>
				<form method="post" action="<?php printf("%s/api/event", $path); ?>">
					<div class="field">
						<label for="name" class="label">Name</label>
						<div class="control">
							<input id="name" class="input" name="name" type="text" />
						</div>
					</div>
					<div class="field">
						<label for="date" class="label">Date</label>
						<div class="control">
							<?php
								//Set the minimum and maximum date times.
								$date = new DateTime();
								$min = $date -> sub(new DateInterval('P5Y')) -> format('Y-m-d');
								$max = $date -> add(new DateInterval('P6Y')) -> format('Y-m-d');
							?>
							<input id="date" type="date" name="date" class="input"
								min="<?php print($min); ?>" max="<?php print($max); ?>" />
						</div>
					</div>
					<div class="field is-grouped">
						<div class="control">
							<button class="button is-link">Submit</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div id="add-family" class="hero is-fullheight is-white is-invisible">
			<div class="section">
				<button class="delete button is-danger is-pulled-right">Close</button>
				<h2 class="subtitle">Add Family</h2>
				<form method="post" action="<?php printf("%s/api/family", $path); ?>">
					<div class="field">
						<label for="name" class="label">Last Name</label>
						<div class="control">
							<input id="name" class="input" name="name" type="text" />
						</div>
					</div>
					<div class="field is-grouped">
						<div class="control">
							<button class="button is-link">Submit</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div id="add-people" class="hero is-fullheight is-white is-invisible">
			<div class="section">
				<button class="delete button is-danger is-pulled-right">Close</button>
				<h2 class="subtitle">Add Person</h2>
				<form method="post" action="<?php printf("%s/api/people", $path); ?>">
					<div class="field">
						<label for="name" class="label">Full Name</label>
						<div class="control">
							<input id="name" class="input" name="name" type="text" />
						</div>
					</div>
					<div class="field">
						<label for="family" class="label">Family</label>
						<div class="control">
							<div class="select">
								<select name="family" class="input">
									<option value="">None</option>
								<?php foreach($families as $family) { ?>
									<option value="<?php print($family -> id); ?>"><?php print($family -> name); ?></option>
								<?php } ?>
								</select>
							</div>
						</div>
					</div>
					<div class="field is-grouped">
						<div class="control">
							<button class="button is-link">Submit</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class="loading is-invisible hero is-fullheight"></div>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script type="text/javascript">
			(function($) {
				//Utilities.
				var error = function(xhr, textStatus, thrownError) {
					$(this).prepend('<div class="message is-danger"><div class="message-header"><p>Error' +
							'</p></div><div class="message-body">' + xhr.responseText + '</div></div>');

					console.error('There was an error processing the request.');
					console.error('XHR/Error: [' + textStatus + '] ' + thrownError + ': ' +
							xhr.responseText);
				};
				var	panels = {
						moveOut: function($s, cb) {
							$s.stop().animate({left: $('html').outerWidth(), opacity: 0}, 400, 'swing', cb);
						},
						moveIn: function($s, cb) {
							$s.stop().show().animate({left: 0, opacity: 1}, 400, 'swing', cb);
						}
				};
				var attendance = {
						update: function() {
							$.ajax({
								url: $('html').data('path') + '/api/attendance/' + $('.attendance').data('event'),
								type: 'GET',
								beforeSend:	function() {
									panels.moveIn($('.loading'));
									$('.attendance').html();
									$('.attendance').find('.message').remove();
								}.bind(this),
								success: function (response) {
									$.each(response.data, function(name, family) {
										var html = '<div class="panel"><h3 class="panel-heading">' + name + '</h3>';

										$.each(family, function(index, person) {
											var button = (!person.attended) ? 'is-danger">Absent' : 'is-success">Present';
											html +=	'<div class="panel-block"><div class="content" style="width: 100%">' +
													'<form class="attend is-pulled-right" method="put" action="' +
													$('html').data('path') + '/api/attendance/' +
													$('.attendance').data('event') + '/' + person.id + '">' +
													'<div class="field is-grouped"><div class="control">' +
													'<button class="button ' + button +
													'</button></div></div></form>' +
													'<strong>' + person.name + '</strong></div></div>';
										});

										html += '</div>';

										$('.attendance').append(html);
									});
								},
								error: function(a, b, c) { error(a, b, c, this); }.bind(this),
								complete:	function() {
									panels.moveOut($('.loading'));

									$('form.attend').submit(function(e) {
										e.preventDefault();

										$.ajax({
											url: $(this).attr('action'),
											type: $(this).attr('method'),
											data: $(this).serialize(),
											beforeSend:	function() {
												$(this).find('.message').remove();
												panels.moveIn($('.loading'));
											}.bind(this),
											success: function (response) {
												if (!response.data) {
													$(this).find('button').removeClass('is-success').addClass('is-danger').
														text('Absent');
												} else {
													$(this).find('button').removeClass('is-danger').addClass('is-success').
														text('Present');
												}
											}.bind(this),
											error: function(a, b, c) { error(a, b, c, this); }.bind(this),
											complete:	function() {
												panels.moveOut($('.loading'));
											}
										});
									});
								}
							});
						}
				}

				$('.is-fullheight').css({position: 'fixed', top: 0, width: '100%'}).each(function() {
					panels.moveOut($(this), function() {
						$(this).removeClass('is-invisible');
					}.bind(this));
				});

				attendance.update();

				$('.load').click(function() {
					panels.moveIn($('#' + $(this).data('load')));
				});

				$('.delete').click(function() {
					panels.moveOut($('#' + $(this).closest('.hero').attr('id')));
				});

				$('form').submit(function(e) {
					e.preventDefault();
					$.ajax({
						url: $(this).attr('action'),
						type: $(this).attr('method'),
						data: $(this).serialize(),
						beforeSend:	function() {
							$(this).find('.message').remove();
							panels.moveIn($('.loading'));
						}.bind(this),
						success: function (response) {
							$(this).prepend('<div class="message is-success"><div class="message-header"><p>' +
									'Success' + '</p></div><div class="message-body">' + response.message + '</div></div>');

							attendance.update();
						}.bind(this),
						error: function(a, b, c) { error(a, b, c, this); }.bind(this),
						complete:	function() {
							panels.moveOut($('.loading'));
						}
					});
				});
			}(jQuery));
		</script>
	</body>
</html>