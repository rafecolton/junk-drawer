var __t;__t=function(ns){var curr,index,part,parts,_i,_len;curr=null,parts=[].concat=ns.split(".");for(index=_i=0,_len=parts.length;_i<_len;index=++_i){part=parts[index];if(curr===null){curr=eval(part);continue}curr[part]==null?curr=curr[part]={}:curr=curr[part]}return curr},function(){var a,b,c;a=function(){function a(){}return a.prototype.to_json=function(){return JSON.stringify(self)},a.prototype.to_dust=function(a){return dust.render(this.className.toLowerCase(),self.to_json,function(b,c){return a(c)})},a}(),b=function(){function b(){}var a;return b.prototype.extend=function(a,b){var c,d;for(d in b)c=b[d],a[d]=c;return a},a=function(a,b){return extend(a.prototype,b)},b}(),c=c||{},c.Chat=function(a){var b,c,d,e,f,g,h,i,j,k,l;d=function(){},d.prototype={location:"http://localhost:8080/rabbit",logged_in:!1},i=this,h=$.extend(i,new d,a),k=null,l=null,g={sign_on:"login",sign_off:"logoff",from_self:"sent",normal_message:"message",start_typing:"begin_typing",stop_typing:"end_typing",new_user:"new_user_received"},b=function(){function a(){}return a}(),e=function(){return"<div id='login'>\n\tPlease sign in to use RabbitChat\n\t<form onsubmit='window.rc.do_login(); return false;'>\n\t\tUsername: <input id='username' type='text' placeholder='username'>\n\t\t<input type='submit' value='Submit'>\n</div>"},c=function(){return"<div id='chat'>\n\t<div id='viewport'></div>\n\t<div id='textport'>\n\t\t<form onsubmit='window.rc.build_message(); return false;'>\n\t\t\t<select id='to'>\n\t\t\t\t<option value='default'>default</option>\n\t\t\t</select>\n\t\t\t<input id='content' type='text' placeholder='message'>\n\t\t\t<input type='submit' value='Send'>\n\t\t</form>\n\t</div>\n</div>"},f=function(a){var b;return console.log("MESSAGE RECEIVED: "+a.data),b=JSON.parse(a.data),dust.render("message",b,function(a,b){return $("#viewport").append(b)})},i.do_login=function(){var a;return a=!0,$("#username").val().match(/^$|\W+/)&&(a=!1,alert("Username cannont be blank and contain non-word characters")),a&&(l=$("#username").val(),k=new SockJS(i.location),k.onopen=function(){var a;return console.log("socket open"),a={message:{type:g.sign_on,username:l}},k.send(JSON.stringify(a))},k.onclose=function(){return console.log("socket closed")},k.onmessage=function(a){return f(a)},$("#login").remove(),$("body").append(c)),!1},i.build_message=function(){var a;return a={message:{type:g.normal_message,to:$("#to").val(),content:$("#content").val()}},j(a)},j=function(a){var b,c;try{switch(a.message.type){case g.sign_on:alert("hello");break;case g.sign_off:l=null,b={message:{content:""+l+" is signing off"}};break;case g.normal_message:b={message:{content:a.message.content,to:a.message.to,type:g.from_self}},c={data:JSON.stringify(b)},f(c);break;case g.begin_typing:b={message:{content:""+l+" is typing"}};break;case g.stop_typing:b={message:{content:""+l+" has stopped typing"}};break;default:b=null}}catch(d){console.log(d),b=null}if(b!=null)return b.message.type=a.message.type,b.message.from=l,k.send(JSON.stringify(b))},i.init=function(){var a,b;return $("body").append(e),a={status:"test1",name:"testname",username:"good"},b={no_status:"test2",no_name:"test-no-name",username:"bad"},dust.render("buddy",a,function(a,b){return console.log(b)}),dust.render("buddy",b,function(a,b){return console.log(b)})}},window.rc=new c.Chat,$(function(){return window.rc.init()})}.call(this)