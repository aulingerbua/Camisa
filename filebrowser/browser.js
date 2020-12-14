/**
 * 
 */

var browserProperties = {
	'browserWindowId' : null,
	'baseDirectory' : null,
	'tree' : null,
	'typesAllowed' : 'text',
	'toLink' : function() {
		var link = '/' + this.tree.join('/');
		return link;
	},
	'toPostParameter' : function() {
		var link = this.tree.join('/');
		return link;
	},
	'toGetParameter' : function() {
		var getParams = '?dir=' + this.tree.join('/');
		return getParams;
	},
	'reset' : function() {
		this.tree = [ this.baseDirectory ]
	},
	'showtree' : function() {
		var treeWoBase = Object.create(this.tree);
		treeWoBase.shift();
		var showtree = treeWoBase.join('/') + '/';
		return showtree;
	}
};

/**
 * Initialize the browser
 * 
 * @param baseDirectory
 *            the directory the browser points to.
 * @param browserWindowId
 *            the ID of the Element the browser will be placed in.
 * @param typesAllowed
 *            the types allowed for files to be uploaded.
 * @returns
 */
function initBrowser(baseDirectory, browserWindowId, typesAllowed) {
	if (!document.getElementById(browserWindowId))
		return;

	properties = Object.create(browserProperties);
	properties.browserWindowId = browserWindowId;
	properties.baseDirectory = baseDirectory;
	properties.tree = [ baseDirectory ];
	if (typesAllowed)
		properties.typesAllowed = typesAllowed;

	// contentsRequest, filesActionRequest;
	ajaxScriptUrl = 'filebrowser/filehandling.php';

	window.addEventListener('load', function() {
		makeControlBar();
		fetchDirectoryContents();
	});
}

/**
 * Creates the controll bar with the buttons.
 * 
 * @returns
 */
function makeControlBar() {
	var ctrl = document.createElement('div');
	ctrl.id = 'ctrl';
	var window = document.createElement('div');
	window.id = 'window';
	window.className = 'window';
	var display = document.createElement('p');
	display.id = 'display';
	var tree = document.createElement('span');
	tree.id = 'tree';
	var info = document.createElement('span');
	info.id = 'info';
	var browser = document.getElementById(properties.browserWindowId);
	browser.appendChild(ctrl);
	browser.appendChild(window);
	window.appendChild(display);
	window.appendChild(info);
	// buttons
	var home = document.createElement('input');
	home.type = 'button';
	home.id = 'home';
	home.addEventListener('click', function() {
		properties.reset();
		fetchDirectoryContents();
		document.getElementById('info').innerHTML = '';
	});

	var back = document.createElement('input');
	back.type = 'button';
	back.id = 'up';
	back.addEventListener('click', function() {
		stepIo('..');
		document.getElementById('info').innerHTML = '';
	});

	var uploadHidden = document.createElement('input')
	uploadHidden.id = 'ule';
	uploadHidden.type = 'file';
	uploadHidden.name = 'upload[]';
	uploadHidden.style.display = 'none';
	uploadHidden.setAttribute('multiple', 'multiple');
	uploadHidden.addEventListener('change', fileUpload);

	var upload = document.createElement('input');
	upload.id = 'upload';
	upload.type = 'button';
	upload.addEventListener('click', function(ev) {
		if (ule)
			ule.click();
		ev.preventDefault();
	}, false);

	var newdir = document.createElement('input');
	newdir.id = 'newdir';
	newdir.type = 'text';
	newdir.className = 'off';
	newdir.value = '';

	var mkdir = document.createElement('input');
	mkdir.id = 'mkdir';
	mkdir.type = 'button';
	mkdir.addEventListener('click',
			function() {
				if (newdir.className == "off") {
					newdir.classList.add('on');
					newdir.focus();
				} else {
					newdir.classList.remove('on');
					var dirName = newdir.value.trim();
					if (!dirName)
						return;
					newdir.value = "";
					var sendData = new FormData();
					sendData.append('dir', properties.toPostParameter() + '/'
							+ dirName);
					sendData.append('action', 'mkdir');
					/*
					 * var url = 'filehandling.php' +
					 * properties.toGetParameter() + '/' + newdir.value +
					 * '&action=mkdir';
					 */
					sendFilehandlingRequest(sendData);
				}
			}, false);

	var trashbin = document.createElement('input');
	trashbin.id = 'trashbin';
	trashbin.type = 'button';
	trashbin.addEventListener('dragenter', function(ev) {
		ev.preventDefault();
		ev.target.style.border = 'solid #ff0';
	}, false);
	trashbin.addEventListener('dragover', function(ev) {
		ev.preventDefault();
	}, false);
	trashbin.addEventListener('drop', function(ev) {
		ev.preventDefault();
		var filename = ev.dataTransfer.getData("text");
		var sendData = new FormData();
		sendData.append('dir', properties.toPostParameter() + '/' + filename);
		sendData.append('action', 'delete');
		var del = confirm('Do you want to delete ' + filename + '?');
		if (del)
			sendFilehandlingRequest(sendData);

		ev.target.style.border = '';
	}, false);
	trashbin.addEventListener('dragleave', function(ev) {
		ev.preventDefault();
		ev.target.style.border = '';
	}, false);

	ctrl.appendChild(home);
	ctrl.appendChild(back);
	ctrl.appendChild(uploadHidden);
	ctrl.appendChild(upload);
	ctrl.appendChild(mkdir);
	ctrl.appendChild(newdir);
	ctrl.appendChild(trashbin);
	ctrl.appendChild(tree);

}

/**
 * Fetches the contents of a directory on the server and displays it in the file
 * browser window.
 * 
 * The root directory of the browsable directory tree is set in the
 * properties.tree property and must be an array even if it contains only one
 * element. The url parameter contains the php script handling the AJAX request.
 * 
 * @param url
 *            the url of a php script
 * @returns
 */
function fetchDirectoryContents() {
	contentsRequest = new XMLHttpRequest();

	if (!contentsRequest) {
		alert('Cannot create an XMLHTTP instance');
		return false;
	}
	contentsRequest.onreadystatechange = function() {
		displayContents();
		makeDirStepable();
		initDragToTrash();
	};
	contentsRequest.open('GET', ajaxScriptUrl + properties.toGetParameter()
			+ '&action=display');
	contentsRequest.send();
}

/**
 * Displays the contents of a directory.
 * 
 * @returns
 */
function displayContents() {
	if (contentsRequest.readyState === XMLHttpRequest.DONE) {
		if (contentsRequest.status === 200) {
			document.getElementById('tree').textContent = properties.showtree();
			document.getElementById('display').innerHTML = formatDirContents(contentsRequest.responseText);
		} else {
			alert('There was a problem with the request.');
		}
	}
}

/**
 * Returns two unordered lists, one for directories, one for files.
 * 
 * @param contents
 *            the JSON string received from the contentsRequest.
 * @returns
 */
function formatDirContents(contents) {
	var obj = JSON.parse(contents);
	var dirs = obj.directories;
	var dirList = "";
	if (dirs) {
		var dirList = "<ul id='dir-listing'>";
		for (i = 0; i < dirs.length; i++)
			dirList += "<li class='clickable' draggable='true'>" + dirs[i].name
					+ "</li>";
		dirList += "</ul>";
	}
	var files = obj.files;
	var textTypes = [ 'txt', 'csv', 'sh', 'csh' ];
	var imgTypes = [ 'jpg', 'jpeg', 'bmp', 'gif', 'tif', 'svg' ];
	var fileList = "";
	var fileType;
	if (files) {
		var fileList = "<ul id='file-listing'>";
		for (i = 0; i < files.length; i++) {
			fileType = 'unknown';
			if (textTypes.indexOf(files[i].type) >= 0)
				fileType = 'text';
			else if (imgTypes.indexOf(files[i].type) >= 0)
				fileType = 'image';
			else if (files[i].type == 'pdf')
				fileType = 'pdf';

			var link = '<a class="' + fileType + '" target="_blank" href="'
					+ properties.toLink() + '/' + files[i].name + '">'
					+ files[i].name + '</a>';
			fileList += "<li>" + link + "</li>";
		}

		fileList += "</ul>";
	}
	return dirList + fileList;
}

/**
 * Step in or out in the directories tree.
 * 
 * Appends or removes an element to the tree array unless there is only one
 * element left. Then the httpRequest is carried out and the new contents
 * displayed. This function is added as event handler to clickable directories.
 * 
 * @param dir
 *            name of the directory
 * @returns
 */
function stepIo(dir) {

	if (dir != "..")
		properties.tree.push(dir);
	else if (properties.tree.length > 1)
		properties.tree.pop();

	fetchDirectoryContents();
	document.getElementById('info').innerHTML = '';
}

/**
 * Appends the stepIo function to list entries in the directory listing. It also
 * renews the browser contents.
 * 
 * @returns false on failure
 */
function makeDirStepable() {
	var dirList = document.getElementById('dir-listing');
	if (dirList) {
		directories = dirList.getElementsByTagName('li');
	} else
		return false;

	for (d = 0; d < directories.length; d++) {
		directories[d].addEventListener('click', function(ev) {
			stepIo(ev.target.textContent);
		});
	}

}

/**
 * Sends a file handling request to the php script with the POST method.
 * 
 * This method is used for uploading and deleting files and making new
 * directories.
 * 
 * @param data
 *            contains the form data
 * @returns
 */
function sendFilehandlingRequest(data) {
	filesActionRequest = new XMLHttpRequest();

	if (!filesActionRequest) {
		alert('Cannot create an XMLHTTP instance');
		return false;
	}
	// filesActionRequest.onreadystatechange = displayContents;
	filesActionRequest.onreadystatechange = function() {
		var infWindow = document.getElementById("info");
		infWindow.innerHTML = filesActionRequest.responseText;

		fetchDirectoryContents();
	}
	filesActionRequest.open('POST', ajaxScriptUrl);
	filesActionRequest.send(data);
}

/**
 * Handles the file upload.
 * 
 * @returns
 */
function fileUpload() {
	var files = this.files;
	var sendData = new FormData();
	sendData.append('tree', properties.toLink());
	sendData.append('types', properties.typesAllowed);
	sendData.append('action', 'upload');
	if (files.length > 1) {
		for (var f = 0; f < files.length; f++) {
			sendData.append('upload[]', files[f]);
		}
	} else
		sendData.append('upload', files[0]);
	sendFilehandlingRequest(sendData);
}

/**
 * Adds a dragstart event to the element for dragging it to the trashbin.
 * 
 * @returns
 */
function initDragToTrash() {
	var filesToTrash = function(items) {
		for (d = 0; d < items.length; d++) {
			items[d].addEventListener('dragstart', function(ev) {
				ev.dataTransfer.effectAllowed = 'move';
				ev.dataTransfer.setData('text/plain', ev.target.textContent);
			}, false);
			items[d].addEventListener('dragend', function(ev) {
				document.getElementById('trashbin').style.background = '';
			}, false);
		}
	}

	var fileList = document.getElementById('file-listing');
	var dirList = document.getElementById('dir-listing');
	if (fileList) {
		files = fileList.getElementsByTagName('a');
		filesToTrash(files);
	}
	if (dirList) {
		dirs = dirList.getElementsByTagName('li');
		filesToTrash(dirs);
	}

}
