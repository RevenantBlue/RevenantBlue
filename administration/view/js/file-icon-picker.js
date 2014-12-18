// JavaScript Document
function getExtensionIcon(fileExtension, size) {
	var extIcon;
	switch(fileExtension) {
		case 'aac': case 'AAC':
			extIcon = 'sprite-aac';
			break;
		case 'ai': case 'AI':
			extIcon = 'sprite-ai';
			break;
		case 'aiff': case 'AIFF':
			extIcon = 'sprite-aiff';
			break;
		case 'avi': case 'AVI':
			extIcon = 'sprite-avi';
			break;
		case 'bmp': case 'BMP':
			extIcon = 'sprite-bmp';
			break;
		case 'c': case 'C':
			extIcon = 'sprite-c';
			break;
		case 'cpp': case 'CPP':
			extIcon = 'sprite-cpp';
			break;
		case 'css': case 'CSS':
			extIcon = 'sprite-css';
			break;
		case 'dat': case 'DAT':
			extIcon = 'sprite-dat';
			break;
		case 'dmg': case 'DMG':
			extIcon = 'sprite-dmg';
			break;
		case 'doc': case 'DOC': case 'docx': case 'DOCX':
			extIcon = 'sprite-doc';
			break;
		case 'dotx': case 'DOTX':
			extIcon = 'sprite-dotx';
			break;
		case 'dwg': case 'DWG':
			extIcon = 'sprite-dwg';
			break;
		case 'dxf': case 'DXF':
			extIcon = 'sprite-dxf';
			break;
		case 'eps': case 'EPS':
			extIcon = 'sprite-eps';
			break;
		case 'exe': case 'EXE':
			extIcon = 'sprite-exe';
			break;
		case 'flv': case 'FLV':
			extIcon = 'sprite-flv';
			break;
		case 'gif': case 'GIF':
			extIcon = 'sprite-gif';
			break;
		case 'h': case 'H':
			extIcon = 'sprite-h';
			break;
		case 'hpp': case 'HPP':
			extIcon = 'sprite-hpp';
			break;
		case 'htm': case 'HTM': case 'html': case 'HTML':
			extIcon = 'sprite-html';
			break;
		case 'ics': case 'ICS':
			extIcon = 'sprite-ics';
			break;
		case 'iso': case 'ISO':
			extIcon = 'sprite-iso';
			break;
		case 'java': case 'JAVA':
			extIcon = 'sprite-java';
			break;
		case 'jpeg': case 'JPEG': case 'jpg': case 'JPG':
			extIcon = 'sprite-jpg';
			break;
		case 'key': case 'KEY':
			extIcon = 'sprite-key';
			break;
		case 'mid': case 'MID':
			extIcon = 'sprite-mid';
			break;
		case 'mp3': case 'MP3':
			extIcon = 'sprite-mp3';
			break;
		case 'mp4': case 'MP4':
			extIcon = 'sprite-mp4';
			break;
		case 'mpg': case 'MPG':
			extIcon = 'sprite-mpg';
			break;
		case 'odf': case 'ODF':
			extIcon = 'sprite-odf';
			break;
		case 'ods': case 'ODS':
			extIcon = 'sprite-ods';
			break;
		case 'odt': case 'ODT':
			extIcon = 'sprite-odt';
			break;
		case 'otp': case 'OTP':
			extIcon = 'sprite-otp';
			break;
		case 'ots': case 'OTS':
			extIcon = 'sprite-ots';
			break;
		case 'ott': case 'OTT':
			extIcon = 'sprite-ott';
			break;
		case 'pdf': case 'PDF':
			extIcon = 'sprite-pdf';
			break;
		case 'php': case 'PHP':
			extIcon = 'sprite-php';
			break;
		case 'png': case 'PNG':
			extIcon = 'sprite-png';
			break;
		case 'ppt': case 'PPT':
			extIcon = 'sprite-ppt';
			break;
		case 'psd': case 'PSD':
			extIcon = 'sprite-psd';
			break;
		case 'py': case 'PY':
			extIcon = 'sprite-py';
			break;
		case 'qt': case 'QT':
			extIcon = 'sprite-qt';
			break;
		case 'rar': case 'RAR':
			extIcon = 'sprite-rar';
			break;
		case 'rb': case 'RB':
			extIcon = 'sprite-rb';
			break;
		case 'rtf': case 'RTF':
			extIcon = 'sprite-rtf';
			break;
		case 'sql': case 'SQL':
			extIcon = 'sprite-sql';
			break;
		case 'tga': case 'TGA':
			extIcon = 'sprite-tga';
			break;
		case 'tgz': case 'TGZ':
			extIcon = 'sprite-tgz';
			break;
		case 'tiff': case 'TIFF':
			extIcon = 'sprite-tiff';
			break;
		case 'txt': case 'TXT':
			extIcon = 'sprite-txt';
			break;
		case 'wav': case 'WAV':
			extIcon = 'sprite-wav';
			break;
		case 'xls': case 'XLS':
			extIcon = 'sprite-xls';
			break;
		case 'xlsx': case 'XLSX':
			extIcon = 'sprite-xlsx';
			break;
		case 'xml': case 'XML':
			extIcon = 'sprite-xml';
			break;
		case 'yml': case 'YML':
			extIcon = 'sprite-yml';
			break;
		case 'zip': case 'ZIP':
			extIcon = 'sprite-zip';
			break;
		default:
			extIcon = 'sprite-_blank';
			break;
	}
	switch(size) {
		case 32:
			extIcon += '-32';
			break;
		case 48:
		 	extIcon += '-48';
			break;
		case 64:
			extIcon += '-64';
			break;	
	}
	return extIcon;
}
