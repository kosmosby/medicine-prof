function status(text) {
	$('#status').text(text);
}

$(document).on('wsclient.on.connecting', function() {
	status("Connecting...");
});

$(document).on('wsclient.on.connected', function() {
	status("Connected");
	$('.oncall').hide();
	$('.nocall').show();
});

$(document).on('wsclient.on.disconnected', function() {
	status("Disconnected");
	$('#login').show();
	$('#logged').hide();
	$('.oncall').hide();
	$('.nocall').show();
});

$(document).on('wsclient.on.loggedin', function(event, data) {
	status("Idle");
	$('#login').hide();
	$('#logged').show();
	$('#user').text("Welcome back, " + data.login + " (ext:" + data.ext + ")");
});

$(document).on('wsclient.on.callstarted', function(event, peer) {
	status("In call");
	$('.peer').text(peer.login + " (ext:" + peer.ext + ")");

	$('.oncall').show();
	$('.nocall').hide();
});

$(document).on('wsclient.on.callprogress', function(event, duration) {
	var t = formatDuration(duration);
	$('.call-timer .time').text(t[0]+":"+t[1]+":"+t[2]);
});

$(document).on('wsclient.on.callended', function(event, peer, duration) {
	status("Idle (last call: " + duration + " sec)");
	$('.oncall').hide();
	$('.nocall').show();
});

$(document).on('click', '[data-action=call-hangup]', function() {
	$(document).trigger("wsclient.action.hangup");
});

$(document).on('click', '[data-action=user-login]', function() {
	$(document).trigger('wsclient.action.login', $(this).data('token'));
});


function formatDuration(s) {
	var t = [
		Math.floor(s / 3600),
		Math.floor(s / 60) % 60,
		s % 60
	];

	for(var i in t)
		if (t[i] < 10)
			t[i] = "0"+t[i];
	return t;
}

$(function() {

	var startWebSocket = function (_token) {

		var token;

		var heartbeat_out;
		var heartbeat_in;
		var heartbeatInterval = 15;
		var heartbeatTimeout = 40;

		var peer = {};

		token = _token;

		$(document).trigger('wsclient.on.connecting');

		ws = new WebSocket(getProtocol()+window.location.host+":8001/");

		var id = randomInt(1000, 9999);

		$(document).on('wsclient.action.hangup', function() {
			ws.send("hangup");
		});

		$(document).on('wsclient.action.login', function(event, _token) {
			token = _token;
			ws.send(token);
		});

		refreshHeartbeat = function() {
			if (typeof heartbeat_in != "undefined")
				clearInterval(heartbeat_in);
			heartbeat_in = setInterval(function() {
				ws.close();
			}, heartbeatTimeout*1000);
		};

		ws.onerror = function() {
			console.log("Socket error: "+id);
		};

		ws.onmessage = function(e) {
			e = JSON.parse(e.data);

			switch (e.cmd) {
			case "ping":
				refreshHeartbeat();
				break;
			case "login":
				$(document).trigger('wsclient.on.loggedin', e.data);
				break;
			case "call":
				peer = e.data;
				peer.callstart = Math.round(new Date().getTime()/1000)-peer.callduration;

				$(document).trigger('wsclient.on.callstarted', peer);

				var updateTimer = function() {
					var duration = Math.round(new Date().getTime()/1000)-peer.callstart;
					$(document).trigger('wsclient.on.callprogress', duration);
				};
				timer = setInterval(updateTimer, 1000);
				updateTimer();

				break;
			case "hangup":
				if (timer != null) {
					clearInterval(timer);
					timer = null;
				}
				$(document).trigger('wsclient.on.callended', [peer, Math.round(new Date().getTime()/1000)-peer.callstart]);
				break;
			}
		};

		ws.onclose = function(event) {
			console.log("Socket closed: "+id);
			clearInterval(heartbeat_out);
			clearInterval(heartbeat_in);
			setTimeout(function() {
				startWebSocket(token)
			}, 1000);

			$(document).trigger('wsclient.on.disconnected');
		};

		ws.onopen = function() {
			console.log("Socket opened: "+id);
			refreshHeartbeat();
			heartbeat_out = setInterval(function() {
				ws.send("ping "+id);
			}, heartbeatInterval*1000);

			if (typeof token != "undefined") {
				ws.send(token);
			}

			$(document).trigger('wsclient.on.connected');

		};

		function randomInt(min, max) {
			return Math.floor(Math.random() * (max - min + 1)) + min;
		}

		function getProtocol() {
			var forceSecure = [];
			var proto = "wss://";
			if (forceSecure.indexOf(window.location.host) < 0)
				proto = window.location.protocol == "http:" ? "ws://" : "wss://";
			return proto;
		}
	};

	startWebSocket();
});