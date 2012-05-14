// siehe tx_mklib_scheduler_EmailFieldProvider
Ext.onReady(function() {
	Ext.select('#task_mklibEmail').on('blur', function(event,element) {
		setValueToAllMklibEmailFields(element.value);
	});
});

function setValueToAllMklibEmailFields(value) {
	Ext.each(Ext.select('#task_mklibEmail'), function(element, index) {
    	element.elements[index].value = value;
	});
}