(function(){dust.register("message",body_0);function body_0(chk,ctx){return chk.section(ctx.get("message"),ctx,{"block":body_1},null);}function body_1(chk,ctx){return chk.write("<div class='message'><span class='").helper("select",ctx,{"block":body_2},{"key":ctx.get("type")}).write("'>").helper("eq",ctx,{"else":body_11,"block":body_13},{"key":ctx.get("type"),"value":"sent"}).write("</span>").reference(ctx.get("content"),ctx,"h").write("</div>");}function body_2(chk,ctx){return chk.helper("eq",ctx,{"block":body_3},{"value":"logoff"}).helper("eq",ctx,{"block":body_4},{"value":"login"}).helper("eq",ctx,{"block":body_5},{"value":"message"}).helper("eq",ctx,{"block":body_6},{"value":"sent"}).helper("eq",ctx,{"block":body_7},{"value":"begin_typing"}).helper("eq",ctx,{"block":body_8},{"value":"end_typing"}).helper("eq",ctx,{"block":body_9},{"value":"new_user_received"}).helper("default",ctx,{"block":body_10},null);}function body_3(chk,ctx){return chk.write("system");}function body_4(chk,ctx){return chk.write("system");}function body_5(chk,ctx){return chk.write("received");}function body_6(chk,ctx){return chk.write("sent");}function body_7(chk,ctx){return chk.write("system-temp");}function body_8(chk,ctx){return chk.write("system-temp");}function body_9(chk,ctx){return chk.write("system");}function body_10(chk,ctx){return chk.write("invisible");}function body_11(chk,ctx){return chk.exists(ctx.get("from"),ctx,{"block":body_12},null);}function body_12(chk,ctx){return chk.reference(ctx.get("from"),ctx,"h").write(": ");}function body_13(chk,ctx){return chk.write("(me): ");}return body_0;})();