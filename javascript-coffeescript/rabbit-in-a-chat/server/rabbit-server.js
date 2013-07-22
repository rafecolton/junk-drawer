var http = require('http');
var sockjs = require('sockjs');
var url = require('url');
var fs = require('fs');
var mime = require('mime');
var httpserver = http.createServer(handler);
var mapping = {}
var message_types = { 
	sign_on: "login",
	sign_off: "logoff",
	normal_message: "message",
	start_typing: "begin_typing",
	stop_typing: "end_typing"
}

var context = require('rabbit.js').createContext('amqp://localhost');
var sockjs_opts = {sockjs_url: "http://cdn.sockjs.org/sockjs-0.3.min.js"};

var hub = sockjs.createServer(sockjs_opts);
hub.installHandlers(httpserver, {prefix: "[/]rabbit"});


context.on('ready',function(){
	var sub = context.socket('SUB');
	sub.setEncoding('utf8');	

	//respond to input from rabbit incoming channel
	sub.on('data', function(msg){

		message = JSON.parse(msg);
		console.log("message received: " + msg);

		try{
			switch (message.message.type){
				case message_types.sign_on:
					for (var user in mapping){
						if (message.message.username != user){
							new_message = {
								message: {
									from: "@system",
									content: message.message.username + " has joined RabbitChat",
									type: message.message.type
								}
							}
							var req = context.socket('REQ');
							req.setEncoding('utf8');
							req.connect(mapping[user],function(){
								req.write(JSON.stringify(new_message));
							})
							req.destroy();
						}
					}
					break;
				case message_types.sign_off:

					break;
				case message_types.normal_message:
					if (mapping.hasOwnProperty(message.message.to)){
						var req = context.socket('REQ');
						req.setEncoding('utf8')
						req.connect(mapping[message.message.to],function(){
							req.write(JSON.stringify(message));
						});
						req.destroy();
					}
					break;

				case message_types.start_typing:
					break;
				case message_types.stop_typing:
					break;
			}
		}catch(e){
			console.log("Invalid Message: " + msg)
		}
		
	});

	//receive from rabbit incoming channel
	sub.connect('incoming');

});


hub.on('connection', function(connection){

	console.log("connection established: " + connection.id);

	var rep = context.socket('REP');

	rep.on('data',function(msg){});

	rep.connect(connection.id.toString(),function(){
		rep.pipe(connection);
	});

	var pub = context.socket('PUB');

	connection.on('close', function(){rep.destroy(); pub.destroy();});

	connection.on('data',function(data){
		console.log("data sent from: " + connection.id);
		message = JSON.parse(data);
		//register username if it is present
		console.log("initial connection: " + JSON.stringify(message));
		if (message.message.type == message_types.sign_on){
			mapping[message.message.username] = connection.id
			console.log("mapping established: " + message.message.username + " => " + connection.id);
		}
	});

	//pipe input from user into the pub socket, which goes into rabbit incoming channel
	pub.connect('incoming', function(){
		connection.pipe(pub);
	});
});	

httpserver.listen(8080, '0.0.0.0');

function handler(req, res){
	var path = url.parse(req.url).pathname;
	switch(path){
		case '/':
			var head = "<!doctype html>\n<html>\n<head>\n<title>RabbitChat</title>\n<script src='http://code.jquery.com/jquery-latest.min.js'></script>\n"
			fs.readdir(__dirname + "/../scripts/", function(err, files){
				if (err) return send404(res);
				for (var i = files.length-1; i>=0; i--){
					if (files[i].split('.').pop() != "js"){
						files.splice(i,1);
					}
				}
				for (var i in files){
					head += "<script src='/scripts/" + files[i] + "'></script>\n";
				}
				fs.readdir(__dirname + "/../scripts/dusts/", function(err2, dusts){
					if (err2) return send404(res);
					for (var i = dusts.length-1; i>=0; i--){
						if (dusts[i].split('.').pop() != "js"){
							dusts.splice(i,1);
						}else{
							head += "<script src='/scripts/dusts/" + dusts[i] + "'></script>\n";
						}
					}
					fs.readdir(__dirname + "/../css/", function(err4, files){
						if (err4) return send404(res);
						for (var i = files.length-1; i>=0; i--){
							if (files[i].split('.').pop() != "css"){
								files.splice(i,1);
							}
							else{
								head += "<link rel='stylesheet' type='text/css' href='/css/" + files[i] + "'>\n";	
							}
						}
						head += "</head>\n";
						tail = "<body>\n</body>\n</html>";
						res.writeHead(200, {'Content-Type': 'text/html'});
						res.write(head + tail, 'utf8');
						res.end();
					});
				});
			});
			break;			
		default: 
			fs.readFile(__dirname + "/../" + path, function(err, data){
				if (err) return send404(res);
				res.writeHead(200, {'Content-Type': mime.lookup(__dirname + "/../" + path)});
				res.write(data, 'utf8');
				res.end();
			});	
	}
}

function send404(res){
	res.writeHead(404);
	res.write('404');
	return res.end();
}
