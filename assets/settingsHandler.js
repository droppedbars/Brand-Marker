/**
 * Created by patrick on 14-02-15.
 */

//TODO: make constants and text pull from a common file as PHP code
function brmrk_addRowOnClick() {
	var rowCounter = document.getElementById('rowCounter');

	var rowName = 'brmrk_options[brand_' + rowCounter.value + ']';

	var newRemoveButton = document.createElement('input');
	newRemoveButton.setAttribute('type', 'button');
	newRemoveButton.setAttribute('class', 'button-primary');
	newRemoveButton.setAttribute('value', '-');
	newRemoveButton.setAttribute('onclick', 'brmrk_removeRowOnClick(\'brmrk_row_' + rowCounter.value + '\')');

	var newBrandText = document.createElement('input');
	newBrandText.setAttribute('type', 'text');
	newBrandText.setAttribute('name', rowName);
	newBrandText.setAttribute('size', '24');

	var blankOption = document.createElement('option');
	blankOption.setAttribute('value', 'BRMRK_BLANK');
	blankOption.text = '';
	blankOption.setAttribute('selected', 'selected');
	var registeredOption = document.createElement('option');
	registeredOption.setAttribute('value', 'BRMRK_REGISTERED');
	registeredOption.text = '®';
	var trademarkOption = document.createElement('option');
	trademarkOption.setAttribute('value', 'BRMRK_TRADE_MARK');
	trademarkOption.text = '™';

	var newSelect = document.createElement('select');
	newSelect.setAttribute('name', 'brmrk_options[mark_' + rowCounter.value + ']');
	newSelect.appendChild(blankOption);
	newSelect.appendChild(registeredOption);
	newSelect.appendChild(trademarkOption);

	var newCaseSensitive = document.createElement('input');
	newCaseSensitive.setAttribute('type', 'checkbox');
	newCaseSensitive.setAttribute('name', 'brmrk_options[case_' + rowCounter.value + ']');

	var newCaseSensitiveLabel = document.createElement('label');
	newCaseSensitiveLabel.htmlFor = 'brmrk_options[case_' + rowCounter.value + ']';
	newCaseSensitiveLabel.appendChild(newCaseSensitive);
	newCaseSensitiveLabel.appendChild(document.createTextNode('Case Sensitive'));

	var newOnceOnly = document.createElement('input');
	newOnceOnly.setAttribute('type', 'checkbox');
	newOnceOnly.setAttribute('name', 'brmrk_options[once_' + rowCounter.value + ']');

	var newOnceOnlyLabel = document.createElement('label');
	newOnceOnlyLabel.htmlFor = 'brmrk_options[once_' + rowCounter.value + ']';
	newOnceOnlyLabel.appendChild(newOnceOnly);
	newOnceOnlyLabel.appendChild(document.createTextNode('Apply Only Once'));

	var newBR = document.createElement('br');

	var newRowDiv = document.createElement('div');
	newRowDiv.setAttribute('id', 'brmrk_row_' + rowCounter.value);
	newRowDiv.appendChild(newRemoveButton);
	newRowDiv.appendChild(document.createTextNode(' '));
	newRowDiv.appendChild(newBrandText);
	newRowDiv.appendChild(document.createTextNode(' '));
	newRowDiv.appendChild(newSelect);
	newRowDiv.appendChild(document.createTextNode(' '));
	newRowDiv.appendChild(newCaseSensitiveLabel);
	newRowDiv.appendChild(document.createTextNode(' '));
	newRowDiv.appendChild(newOnceOnlyLabel);
	newRowDiv.appendChild(document.createTextNode(' '));
	newRowDiv.appendChild(newBR);

	var mainDiv = document.getElementById('brmrk_brandRows');
	mainDiv.appendChild(newRowDiv);

	rowCounter.value = ++rowCounter.value;
}

function brmrk_removeRowOnClick(row) {
	var mainDiv = document.getElementById('brmrk_brandRows');
	var theRow = document.getElementById(row);
	mainDiv.removeChild(theRow);
}