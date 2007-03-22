formFlygHotell._visible = true;
formEvenemang._visible = false;
formHotell._visible = false;

radioButtonListener = new Object();
radioButtonListener.click = function (evt) {

	if (evt.target.selection.data == 'Flyg')
	{
		formFlygHotell._visible = true;
		formEvenemang._visible = false;
		formHotell._visible = false;
	} else if (evt.target.selection.data == 'Hotell')
	{
		formFlygHotell._visible = false;
		formEvenemang._visible = false;
		formHotell._visible = true;
	} else if (evt.target.selection.data == 'Evenemang')
	{
		formFlygHotell._visible = false;
		formEvenemang._visible = true;
		formHotell._visible = false;
	} else if (evt.target.selection.data == 'FlygHotell')
	{
		formFlygHotell._visible = true;
		formEvenemang._visible = false;
		formHotell._visible = false;
	}
}
radioGroup.addEventListener("click", radioButtonListener);


_level0.formFlygHotell.Knapp.clickHandler = function() {
	if (_level0.radioGroup.selection.data == 'FlygHotell') {
		trace('flyg & hotell - click');
	} else {
		trace("flyg - click");
	}
}

_level0.formHotell.Knapp.clickHandler = function() {
	trace("hotell - click");
}

_level0.formEvenemang.Knapp.clickHandler = function() {
	trace("evenemang - click");
	
	//L�s in data fr�n f�ljande element:
	//Stad - dropdown
	//Kategori - dropdown
	//KategoriText - textf�lt
	
	trace('stad: ' + _level0.formEvenemang.Stad.selectedItem.label);
	trace('kategori: ' + _level0.formEvenemang.Kategori.selectedItem.label);
	trace('kategoriText: ' + _level0.formEvenemang.KategoriText.text);
}