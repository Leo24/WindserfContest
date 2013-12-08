/* For Greybox 5.54 - 22/06/2011 */
var loading = AJS.$('loading');
var gb_type = GB.type;
var gb_url = GB.url;

// Start loading in the iframe
if (gb_type == "page") {
	document.write('<iframe id="GB_frame" src="' + gb_url + '" frameborder="0"></iframe>');
} else {
	var img_holder = new Image();
	img_holder.src = gb_url;
	document.write('<img id="GB_frame" src="' + gb_url + '">');
}
var frame = AJS.$('GB_frame');

/* Split */
function setupOuterGB() {
	frame.style.visibility = 'visible';
	GB.setFrameSize();
	GB.setWindowPosition();
}
function loaded() {
	AJS.removeElement(loading);
	GB.overlay.innerHTML += "&nbsp;"; // Safari bugfix
	if (gb_type == "image") {
		if (img_holder.width != 0 && img_holder.height != 0) {
			var width = img_holder.width;
			var height = img_holder.height;
			GB.width = width;
			GB.height = height;
			setupOuterGB();
			if (GB.use_fx) {
				AJS.setOpacity(frame, 0);
				AJS.fx.fadeIn(frame);
			}
		}
	} else {
		GB.width = frame.offsetWidth;
		GB.height = frame.offsetHeight;
		setupOuterGB();
	}
}
if (GB.show_loading) {
	AJS.AEV(window, 'load', function(e) {
		loaded();
	});
} else {
	loaded();
}
