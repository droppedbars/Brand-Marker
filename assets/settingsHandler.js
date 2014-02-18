/**
 * Created by patrick on 14-02-15.
 */

function brmrk_addRowOnClick() {
	var rowCounter = document.getElementById('rowCounter');
	rowCounter.value = rowCounter.value++;
	var rowName = 'brmrk_options[brand_'+rowCounter.value+']';

	var newRemoveButton = document.createElement('input');
	newRemoveButton.setAttribute('type', 'button');
	newRemoveButton.setAttribute('class', 'button-primary');
	newRemoveButton.setAttribute('value', '-');
	newRemoveButton.setAttribute('onclick', 'brmrk_removeRowOnClick(\'brmrk_row_'+rowCounter.value+'\')');

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
	newSelect.setAttribute('name', 'brmrk_options[mark_'+rowCounter.value+']');
	newSelect.appendChild(blankOption);
	newSelect.appendChild(registeredOption);
	newSelect.appendChild(trademarkOption);

	//echo '			<label><input type="checkbox" name="' . BRMRK_MARKS . '[case_' . $i . ']" value="' . BRMRK_CASE_SENSITIVE . '" ' . checked( $brand[$i]->is_case_sensitive(), true, false ) . '>Case Sensitive</label>';
	//echo '			<label><input type="checkbox" name="' . BRMRK_MARKS . '[once_'.$i . ']" value="' . BRMRK_ONCE_ONLY . '" ' . checked( $brand[$i]->apply_only_once(), true, false ) . '>Apply Only Once</label>';


	var newBR = document.createElement('br');

	var newRowDiv = document.createElement('div');
	newRowDiv.setAttribute('id', 'brmrk_row_' + rowCounter.value);
	newRowDiv.appendChild(newRemoveButton);
	newRowDiv.appendChild(newBrandText);
	newRowDiv.appendChild(newSelect);
	newRowDiv.appendChild(newBR);

	var mainDiv = document.getElementById('brmrk_brandRows');
	mainDiv.appendChild(newRowDiv);
}

function brmrk_removeRowOnClick(row) {
	var mainDiv = document.getElementById('brmrk_brandRows');
	var theRow = document.getElementById(row);
	mainDiv.removeChild(theRow);
}