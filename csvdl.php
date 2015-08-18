<?php $csvfilepath = TEMPL_PLUGIN_URL."tmplconnector/monetize/templatic-bulk_upload/post_sample.csv";
	wp_redirect($csvfilepath);
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header("Content-Type: image/png");
	header("Content-type: application/force-download");
	header('Content-Disposition: attachment; filename="post_sample.csv"');
	header('Content-Transfer-Encoding: binary');
	readfile($csvfilepath);
	exit;?>