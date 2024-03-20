
//capitalise initial letter
function capital(str) {
	if (str) {
		var c = str.charAt(0).toUpperCase();
		str = str.substring(1);
		return c + str;
	}
	return '';
}
//AFTER https://eloquentjavascript.net/code/#4.1
function range(start, end, step) {
	var array = [],
	i;
	step = step || 1;
	if (step > 0) {
		for (i = start; i <= end; i += step) {
			array.push(i);
		}
	} else {
		for (i = start; i >= end; i += step) {
			array.push(i);
		}
	}
	return array;
}

function groupOddsAndEvens(arr) {
	var odd = [],
		even = [],
		i = 0,
		L = arr.length;
	for (; i < L; i++) {
		if (arr[i] % 2) {
			odd.push(arr[i]);
		} else {
			even.push(arr[i]);
		}
	}
	return [odd, even];
}

function stitchUp(odd, even) {
	var grp = [],
		i = 1,
		tmp;
	while (odd[0]) {
		if (i % 2) {
			tmp = [];
			tmp.push(odd.shift());
			tmp.unshift(even.pop());
			grp.push(tmp);
		} else {
			tmp = [];
			tmp.push(odd.pop());
			tmp.unshift(even.shift());
			grp.push(tmp);
		}
		i++;
	}
	return grp;
}

//alas no reduce method
function flatten(arr) {
	var ret = [],
		i = 0,
		L = arr.length;
	for (; i < L; i++) {
		ret.push(arr[i][0], arr[i][1]);
	}
	return ret;
}

function process(place, select) {
	var col = this.dialogColumns.add(),
		row = col.dialogRows.add();
	col = row.dialogColumns.add();
	col.staticTexts.add(place);
	col = row.dialogColumns.add();
	return col.dropdowns.add(select);
}

function myChooseDocument() {
	var myDocumentNames = [],
		myChooseDocumentDialog,
		myChooseDocumentDropdown,
		myResult,
		i = 0,
		choose = {
			name: "Choose a Document",
			canCancel: false
		},
		place = {
			staticLabel: "Place PDF in:"
		},
		select = {
			stringList: myDocumentNames,
			selectedIndex: 0
		},
		L = app.documents.length;
	//myDocumentNames.push("New Document");
	//Get the names of the documents
	for (i; i < L; i++) {
		myDocumentNames.push(app.documents.item(i).name);
	}
	myChooseDocumentDialog = app.dialogs.add(choose);
	myChooseDocumentDropdown = process.call(myChooseDocumentDialog, place, select);
	myResult = myChooseDocumentDialog.show();
	if (myResult) {
		if (myChooseDocumentDropdown.selectedIndex === 0) {
			myDocument = app.documents.add();
			myNewDocument = true;
		} else {
			myDocument = app.documents.item(myChooseDocumentDropdown.selectedIndex - 1);
			myNewDocument = false;
		}
		myChooseDocumentDialog.destroy();
	} else {
		myDocument = "";
		myNewDocument = "";
		myChooseDocumentDialog.destroy();
	}
	return [myDocument, myNewDocument];
}

function getDoc(exists) {
	var myTemp,
		myDoc,
		myNewDoc;
	if (exists) {
		myTemp = myChooseDocument(),
			myDoc = myTemp[0];
			//boolean, true if new doc, false existing
			myNewDoc = myTemp[1];
		return {
			getPage: function() {
				return myDoc.pages.item(0);
			},
			doc: myDoc
		};
	} else {
		myDoc = app.documents.add();
		return {
			getPage: function() {
				return myChoosePage(myDoc);
			},
			doc: myDoc
		};
	}
}

function myChoosePage(myDocument) {
	//alert(myDocument.name);
	var i = 0,
		L = myDocument.pages.length,
		myPageNames = [],
		myChoosePageDialog,
		myChoosePageDropdown,
		columns,
		col,
		row,
		myPage;
	//Get the names of the pages in the document
	for (i = 0; i < L; i++) {
		myPageNames.push(myDocument.pages.item(i).name);
	}
	myChoosePageDialog = app.dialogs.add({
		name: "Choose a Page",
		canCancel: false
	});
	columns = myChoosePageDialog.dialogColumns.add();
	row = columns.dialogRows.add();
	col = row.dialogColumns.add()
	col.staticTexts.add({
		staticLabel: "Place PDF on:"
	});
	col = row.dialogColumns.add();
	myChoosePageDropdown = col.dropdowns.add({
		stringList: myPageNames,
		selectedIndex: 0
	});
	myChoosePageDialog.show();
	myPage = myDocument.pages.item(myChoosePageDropdown.selectedIndex);
	myChoosePageDialog.destroy();
	return myPage;
}

function myPlacePDF(myDocument, myPage, myPDFFile, rhpage, count) {
	var pos,
		myCounter = 1,
		grp = range(1, count),
		res = groupOddsAndEvens(grp),
		impo = flatten(stitchUp(res[0], res[1])),
		m = capital(prompt('crop options: Art, Bleed, Crop, Media, Trim, ', 'Media')) || 'Media';
	app.pdfPlacePreferences.pdfCrop = PDFCrop['crop' + m];
	while (impo[0]) {
		if ((myCounter > 1) && (myCounter % 2)) {
			myPage = myDocument.pages.add(LocationOptions.after, myPage);
		}
		app.pdfPlacePreferences.pageNumber = impo.shift();
		pos = (myCounter % 2) ? 0 : rhpage;
		myPage.place(File(myPDFFile), [pos, 0])[0];
		myCounter++;
	} //while
}

function main() {
	//Make certain that user interaction (display of dialogs, etc.) is turned on.
	app.scriptPreferences.userInteractionLevel = UserInteractionLevels.interactWithAll;
	//Display a standard Open File dialog box.
	var myDoc,
		myPDFFile = File.openDialog("Choose a PDF File"),
		offset = prompt('offset', 210) || 210,
		//offset = 210,
		count = prompt('page count');
	if (myPDFFile) {
		myDoc = getDoc(app.documents.length);
		myPlacePDF(myDoc.doc, myDoc.getPage(), myPDFFile, offset, count);
	}
}
main();