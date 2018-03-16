jQuery.veritae_importador = {
	
	import_data: {
		continue: true,
		start: 0,
		interval: 200
	},
	
	import_artigos: function(){
		this.loop('veritae_importador_artigos');
	},
	import_materias: function(){
		this.loop('veritae_importador_materias');
	},
	import_noticias: function(){
		this.loop('veritae_importador_noticias');
	},
	import_lex: function(type){
		this.loop('veritae_importador_lex_' + type);
	},
	loop: function(action) {
		console.log('started ' + action);
		
		do {
			this.send_request(action, this.import_data.start, this.import_data.interval);
		} while (this.import_data.continue);

		console.log('finished ' + action);
	},
	send_request: function(action, limit_start, limit_interval) {
		jQuery.ajax({
			url: veritae_importador_src.ajaxurl,
			type: 'post',
			data: {
				action: action,
				start: limit_start,
				interval: limit_interval
			},
			success: function(response){
				console.log(response);
				
				this.import_data.start += this.import_data.interval;
				
				if(response.size === 0) {
					this.import_data.continue = false;
				}
			}.bind(this),
			dataType: 'json',
			async: false,
		});
	}
};

