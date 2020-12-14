/* this script adds the extend button
* to articles
*/
(function() {
	const container = document.getElementById("mainframe-2cols");
	var artcls = container.querySelectorAll("article");

	artcls.forEach(function(ar) {
		let more = document.createElement("div");
		more.className = "more-tag";
		more.innerText = "mehr ...";
		more.addEventListener("click", function() {
			if (more.innerText == "mehr ...") {
				more.innerText = "weniger ...";
				ar.style.height = "auto";
				ar.style.width = "100%";
			} else {
				more.innerText = "mehr ...";
				ar.style.height = "";
				ar.style.width = "";
			}
		})
		ar.appendChild(more);
	});
})();