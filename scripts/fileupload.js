/* This script requires the following variables to be pre-defined:
	fileInputVals
	allowedExtensions
	errors
	fileUploadSuccess
	maxFileSize

	The following variables are optional:
	errorsFromGet
 */

/**
 * Client side file validation
 * @returns {boolean}
 */
function validateFiles() {
	var i = 1;
	$(fileUploadSelector).each(function() {

		var filePath = $(this).val();
		
		if (filePath != '') {
			// If there are more than one file input tags in this form
			if($(fileUploadSelector).length > 1){

				if(!allowedFileType(filePath)){
					errors.push('Item ' + i + ' is not an allowed file type');
				} else if ($(this).get(0).files[0].size > maxFileSize) {
					errors.push('Item ' + i + ' is too large');
				}

			} else { // If there is only one file input tag in this form

				if(!allowedFileType(filePath)){
					errors.push('File type not allowed');
				}else if($(this).get(0).files[0].size > maxFileSize){
					errors.push('File is too large');
				}
			}
		}
		i++;
	});

	if (errors.length != 0) {
		var errorString = (errors.length == 1) ? errors[0] : errors.join("<br/>");
		$(fileUploadNotificationSelector).html(errorString);
		$(fileUploadNotificationSelector).addClass('error-text');
		errors = [];
		return false;
	}

	return true;
}

/**
 * Checks if the file uploaded contains an allowed extension
 * @param filePath string
 * @returns {boolean}
 */
function allowedFileType(filePath) {
	var periodIndex = filePath.lastIndexOf('.');
	var ext = filePath.substring(periodIndex + 1);
	var inArray = false;
	for(var i = 0; i < allowedExtensions.length; i++){
		if(ext == allowedExtensions[i]){
			inArray = true;
			break;
		}
	}
	return inArray;
}

/**
 * Handler for the form on submit event on make and play
 * @returns {boolean}
 */
function onFilesSubmit() {
	return validateFiles();
}