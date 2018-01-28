<!DOCTYPE html>
<html>
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
						<li class="navbar-item"><button class="load button is-link" data-load="add-people">
							Add Person
						</button></li>
						<li class="navbar-item"><button class="load button is-info" data-load="add-family">
							Add Family
						</button></li>
					</ul>
				</nav>
			</header>
		</main>
		<div id="add-people" class="hero is-fullheight is-white is-invisible">
			<div class="section">
				<button class="delete button is-danger is-pulled-right">Close</button>
				<h2 class="subtitle">Add Person</h2>
				<form action="post" method="<?php printf("%s/api/people", $path); ?>">
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
								<select name="family">
								<?php foreach($family as $fam) { ?>
									<option value="<?php print($fam -> id); ?>"><?php print($fam -> name); ?></option>
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
		<div id="add-family" class="hero is-fullheight is-white is-invisible">
			<div class="section">
				<button class="delete button is-danger is-pulled-right">Close</button>
				<h2 class="subtitle">Add Family</h2>
				<form action="post" method="<?php printf("%s/api/family", $path); ?>">
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
		<div class="loading is-invisible hero is-fullheight"></div>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script type="text/javascript">
		(function($) {
			var	utilities = {
					panels: {
						moveOut: function($s, cb) {
							$s.stop().animate({left: $('html').outerWidth(), opacity: 0}, 400, 'swing', cb);
						},
						moveIn: function($s, cb) {
							$s.stop().show().animate({left: 0, opacity: 1}, 400, 'swing', cb);
						}
					}
			};

			$('.is-fullheight').css({position: 'fixed', top: 0, width: '100%'}).each(function() {
				utilities.panels.moveOut($(this), function() {
					$(this).removeClass('is-invisible');
				}.bind(this));
			});

			$('.load').click(function() {
				utilities.panels.moveIn($('#' + $(this).data('load')));
			});

			$('.delete').click(function() {
				utilities.panels.moveOut($('#' + $(this).closest('.hero').attr('id')));
			});

			$('form').submit(function(e) {
				e.preventDefault();
				$.ajax({
					url: $(this).attr('action'),
					type: $(this).attr('method'),
					data:$(this).serialize(),
					beforeSend:	function() {
						$(this).find('.message').remove();
						utilities.panels.moveIn($('.loading'));
					}.bind(this),
					success: function () {
						$(this).prepend('<div class="message is-danger"><div class="message-header"><p>' +
								'Success' + '</p></div><div class="message-body">' +
								'This form was successfully submitted.' +
								'</div></div>');
					}.bind(this),
					error: function(xhr, textStatus, thrownError) {
						$(this).prepend('<div class="message is-danger"><div class="message-header"><p>' +
								'Error' + '</p></div><div class="message-body">' +
								'There was an error submitting this form.' +
								'</div></div>');

						console.error('There was an error processing the request.');
						console.error('XHR/Error: [' + textStatus + '] ' + xhr.responseText);
						console.error(thrownError);
					}.bind(this),
					complete:	function() {
						utilities.panels.moveOut($('.loading'));
					}
				});
			});
		}(jQuery));
		</script>
	</body>
</html>