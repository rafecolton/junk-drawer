RABBITCHAT = RABBITCHAT or {}
RABBITCHAT.Chat = (options) ->
	defaultOpts = ->
	defaultOpts:: =
		location: "http://localhost:8080/rabbit"
		logged_in: false
		
	self = this
	opts = $.extend(self, new defaultOpts(), options)
	sock = null
	username = null
	message_types = 
		sign_on: "login"
		sign_off: "logoff"
		from_self: "sent"
		normal_message: "message"
		start_typing: "begin_typing"
		stop_typing: "end_typing"
		new_user: "new_user_received"

	class Message 
		#include Dustable

	#private methods
	login_form = -> """
			<div id='login'>
				Please sign in to use RabbitChat
				<form onsubmit='window.rc.do_login(); return false;'>
					Username: <input id='username' type='text' placeholder='username'>
					<input type='submit' value='Submit'>
			</div>
		"""

	build_chat = -> """
			<div id='chat'>
				<div id='viewport'></div>
				<div id='textport'>
					<form onsubmit='window.rc.build_message(); return false;'>
						<select id='to'>
							<option value='default'>default</option>
						</select>
						<input id='content' type='text' placeholder='message'>
						<input type='submit' value='Send'>
					</form>
				</div>
			</div>
		"""

	message_received = (msg) ->
		console.log("MESSAGE RECEIVED: " + msg.data);
		message = JSON.parse(msg.data)
		dust.render "message", message, (err, output) ->
			$('#viewport').append output

	self.do_login = ->
		ret = true
		if $('#username').val().match(/^$|\W+/)
			ret = false 
			alert "Username cannont be blank and contain non-word characters"
		if ret
			username = $('#username').val()
			sock = new SockJS(self.location)
			sock.onopen = ->
				console.log "socket open"
				message =
					message:
						type: message_types.sign_on
						username: username
				sock.send JSON.stringify(message)
			sock.onclose = ->
				console.log "socket closed"
			sock.onmessage = (msg) -> message_received(msg)
			$('#login').remove()
			$('body').append build_chat
			# $('#to').select2
			# 	placeholder: "Select a User"
			# 	dropDownCSS: "border: none; width: 100;"
		false

	self.build_message = ->
		message =
			message:
				type: message_types.normal_message
				to: $('#to').val()
				content: $('#content').val()
		send_message message

	send_message = (options) ->
		try
			switch options.message.type
				when message_types.sign_on
					alert "hello"

				when message_types.sign_off
					username = null
					message = 
						message: 
							content: "#{username} is signing off"

				when message_types.normal_message
					message = 
						message: 
							content: options.message.content
							to: options.message.to
							type: message_types.from_self
					self_message =
						data: JSON.stringify(message)
					message_received self_message


				when message_types.begin_typing
					message = 
						message: 
							content: "#{username} is typing"

				when message_types.stop_typing
					message = 
						message: 
							content: "#{username} has stopped typing"

				else
					message = null
		catch e
			console.log(e)
			message = null
		if message?
			message.message.type = options.message.type
			message.message.from = username
			sock.send(JSON.stringify(message))

	self.init = ->
		$('body').append login_form
		test = 
			status: "test1"
			name: "testname"
			username: "good"
		test2 = 
			no_status: "test2"
			no_name: "test-no-name"
			username: "bad"
		dust.render "buddy", test, (err, output) ->
			console.log(output)
		dust.render "buddy", test2, (err, output) ->
			console.log(output)
		
	return

window.rc = new RABBITCHAT.Chat()
$ ->
	window.rc.init()
