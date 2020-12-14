/**
 * animate the top navigation bar
 */
(function() {
	let navi = document.getElementById("top-navi");
	let logo = document.getElementById("logo");
	// let bg = document.getElementById("background");
	// let rel = bg.scrollHeight;
	window.addEventListener("scroll", function() {
		let vertscroll = Math.floor(window.scrollY);
		if (vertscroll > 1) {
			logo.style.height = "65px";
			navi.classList.add("scrolled");
		} else {
			logo.style.height = "";
			navi.classList.remove("scrolled");
		}
	}, false)
	/*window.addEventListener("scroll", function() {
		let fade = Math.floor(window.scrollY) / rel;
		bg.style.opacity = 1 - Math.min(1, fade * 2.1);
	}, false)*/
})();