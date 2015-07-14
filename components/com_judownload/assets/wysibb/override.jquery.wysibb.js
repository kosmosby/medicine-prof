var judlWbbOpt = {
	minheight : 100,
	maxCommentChar : 1000,
	minCommentChar : 20,
	allButtons: {
		readmore : {
			title     : Joomla.JText._('READMORE','Readmore'),
			buttonHTML: '<span class="btn-inner modesw readmore-editor">[Readmore]</span>',
			groupkey  : 'align',
			cmd       : function (command, value, queryState) {
				if (queryState) {
					this.wbbRemoveCallback(command,true);
				}else{
					var content = jQuery(this.body).html();
					if (content.match(/<hr\s+id=(\"|')system-readmore(\"|')\s*\/*>/i)) {
						alert(Joomla.JText._('COM_JUDOWNLOAD_READMORE_WYSIBB_ALREADY_EXISTS', 'There is already a Read more... link that has been inserted. Only one such link is permitted.'));
						return false;
					} else {
						this.wbbInsertCallback(command);
					}
				}
			},
			transform : {
				'<hr id="system-readmore" />': '[READMORE]'
			}
		},
		video       : {
			title     : CURLANG.video,
			buttonHTML: '<span class="fonticon ve-tlb-video1">\uE008</span>',
			modal     : {
				title   : CURLANG.video,
				width   : "500px",
				tabs    : [
					{
						title: CURLANG.video,
						input: [
							{param: "SRC", title: CURLANG.modal_video_text}
						]
					}
				],
				onSubmit: function (cmd, opt, queryState) {
					var url = this.$modal.find('input[name="SRC"]').val();
					if (url) {
						url = url.replace(/^\s+/, "").replace(/\s+$/, "");
					}

					var youtubeRegexp = /(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'<>\/\s]+)(?:$|\/|\?|\&)?/i;
					var vimeoRegexp = /https?:\/\/(?:www\.)?vimeo.com\/(\d+)(?:$|\/|\?)?/i;
					var a;
					if (url.match(youtubeRegexp) && url.match(youtubeRegexp).length == 2) {
						a = url.match(youtubeRegexp)[1];
						var code = "www.youtube.com/embed/" + a;
						this.insertAtCursor(this.getCodeByCommand(cmd, {src: code}));
						this.closeModal();
					} else if (url.match(vimeoRegexp) && url.match(vimeoRegexp).length == 2) {
						a = url.match(vimeoRegexp)[1];
						var code = "player.vimeo.com/video/" + a;
						this.insertAtCursor(this.getCodeByCommand(cmd, {src: code}));
						this.closeModal();
					} else {
						this.$modal.find('span.wbbm-inperr').remove();
						this.$modal.find('input[name="SRC"]').after('<span class="wbbm-inperr">' + CURLANG.validation_err + '</span>').addClass("wbbm-brdred");
					}

					this.updateUI();
					return false;
				}
			},
			transform : {
				'<iframe src="http://{SRC}" width="380" height="300" frameborder="0"></iframe>': '[video]{SRC}[/video]'
			}
		}
	},
	smileList       : [
		{title: CURLANG.sm2, img: '<img src="{themePrefix}{themeName}/img/smiles/devil.png"> ', bbcode: "3:)"},
		{title: 'Smile', img: '<img src="{themePrefix}{themeName}/img/smiles/smile.png"> ', bbcode: ":)"},
		{title: 'Frown', img: '<img src="{themePrefix}{themeName}/img/smiles/frown.png"> ', bbcode: ":("},
		{title: CURLANG.sm4, img: '<img src="{themePrefix}{themeName}/img/smiles/tongue.png"> ', bbcode: ":P"},
		{title: CURLANG.sm5, img: '<img src="{themePrefix}{themeName}/img/smiles/grin.png"> ', bbcode: ":D"},
		{title: CURLANG.sm6, img: '<img src="{themePrefix}{themeName}/img/smiles/angry.png"> ', bbcode: ">:o"},
		{title: CURLANG.sm6, img: '<img src="{themePrefix}{themeName}/img/smiles/gasp.png"> ', bbcode: ":o"},
		{title: CURLANG.sm6, img: '<img src="{themePrefix}{themeName}/img/smiles/wink.png"> ', bbcode: ";)"},
		{title: CURLANG.sm7, img: '<img src="{themePrefix}{themeName}/img/smiles/unsure.png"> ', bbcode: ":-/"},
		{title: CURLANG.sm7, img: '<img src="{themePrefix}{themeName}/img/smiles/pacman.png"> ', bbcode: ":v"},
		{title: CURLANG.sm9, img: '<img src="{themePrefix}{themeName}/img/smiles/cry.png"> ', bbcode: ":'("},
		{title: CURLANG.sm9, img: '<img src="{themePrefix}{themeName}/img/smiles/kiki.png"> ', bbcode: "^_^"},
		{title: CURLANG.sm9, img: '<img src="{themePrefix}{themeName}/img/smiles/glasses.png"> ', bbcode: "8-)"},
		{title: CURLANG.sm9, img: '<img src="{themePrefix}{themeName}/img/smiles/heart.png"> ', bbcode: "<3"},
		{title: CURLANG.sm9, img: '<img src="{themePrefix}{themeName}/img/smiles/squinting.png"> ', bbcode: "-_-"},
		{title: CURLANG.sm9, img: '<img src="{themePrefix}{themeName}/img/smiles/confused.png"> ', bbcode: "o.O"},
		{title: CURLANG.sm9, img: '<img src="{themePrefix}{themeName}/img/smiles/colonthree.png"> ', bbcode: ":3"},
		{title: CURLANG.sm9, img: '<img src="{themePrefix}{themeName}/img/smiles/like.png"> ', bbcode: "(y)"}
	]
};

(function ($) {
	$.wysibb.prototype.charCount  = function () {
		return this.$txtArea.val().length;
	};

	$.wysibb.prototype._insertAtCursor = $.wysibb.prototype.insertAtCursor;
	$.wysibb.prototype.insertAtCursor = function(code,forceBBMode) {
		this._insertAtCursor(code,forceBBMode);
		this.sync(true);
		Joomla.commentFormId = $(this.$txtArea).closest('form').attr('id');
		$(this.txtArea).trigger('blur');
	};

	$.wysibb.prototype.isValidateChars =  function () {
		var leng = this.charCount();

		if (leng < this.options.minCommentChar || leng > this.options.maxCommentChar) {
			return false;
		}
		return true;
	};

	$.wysibb.prototype.getBBCode = function(noRemoveLastBodyBR) {
		if (!this.options.rules) {return this.$txtArea.val();}
		if (this.options.bbmode) {return this.$txtArea.val();}
		this.clearEmpty();
		if(!noRemoveLastBodyBR){
			this.removeLastBodyBR();
		}
		return this.toBB(this.$body.html());
	};

	$.wysibb.prototype.sync = function(noRemoveLastBodyBR) {
		if (this.options.bbmode) {
			this.$body.html(this.getHTML(this.txtArea.value,true));
		}else{
			this.$txtArea.attr("wbbsync",1).val(this.getBBCode(noRemoveLastBodyBR));
		}
	};

	$.wysibb.prototype.clean = function() {
		$(this.$txtArea).val('');
		this.options.bbmode = true;
		this.sync(true);
		this.options.bbmode = false;
	};

	$.fn.sync = function(noRemoveLastBodyBR) {
		this.data("wbb").sync(noRemoveLastBodyBR);
		return this.data("wbb");
	};

	$.fn.isValidateChars = function() {
		return this.data('wbb').isValidateChars();
	};

	$.fn.clean = function() {
		return this.data('wbb').clean();
	}

})(jQuery);

jQuery(document).ready(function($){
	setTimeout(function(){
		$('.comment-editor.wysibb-texarea').each(function(){
			var wbb = $(this).data('wbb');
			if(wbb){
				$(wbb.body).bind("blur", function (e) {
					Joomla.commentFormId = $(wbb.txtArea).closest('form').attr('id');
					$(wbb.txtArea).trigger('blur');
				});

				$(wbb.body).bind("keypress, keydown, keyup", function (e) {
					setTimeout(function(){
						Joomla.commentFormId = $(wbb.txtArea).closest('form').attr('id');
						wbb.sync(true);
					}, 300);

					setTimeout(function(){
						$(wbb.txtArea).trigger('blur');
					}, 500);
				});

				wbb.sync(true);
			}
		});
	}, 0);
});