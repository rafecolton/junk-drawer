#require (mixins.coffee).Mixins
class Dustable
	to_json: ->
		JSON.stringify self
	to_dust: (callback) ->
		dust.render this.className.toLowerCase(), self.to_json, (err, output) ->
			callback(output) 