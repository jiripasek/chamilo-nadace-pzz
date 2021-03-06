<?php
/* See license terms in /license.txt */
/**
*	This is the export library for Chamilo.
*	Include/require it in your code to use its functionality.
*
*	Several functions below are adaptations from functions distributed by www.nexen.net
*
*	@package chamilo.library
*/
/**
 * Code
 */
require_once 'document.lib.php';
/**
 *
 * @package chamilo.library
 */
class Export {

	private function __construct() {

	}

	/**
	 * Export tabular data to CSV-file
	 * @param array $data
	 * @param string $filename
	 */
	public static function export_table_csv ($data, $filename = 'export') {
		$file = api_get_path(SYS_ARCHIVE_PATH).uniqid('').'.csv';
		$handle = @fopen($file, 'a+');

		if(is_array($data)) {
			foreach ($data as $index => $row) {
				$line = '';
				if(is_array($row)) {
					foreach($row as $value) {
						$line .= '"'.str_replace('"', '""', $value).'";';
					}
				}
				@fwrite($handle, $line."\n");
			}
		}
		@fclose($handle);
		DocumentManager :: file_send_for_download($file, true, $filename.'.csv');
		return false;
	}

	/**
	 * Export tabular data to XLS-file
	 * @param array $data
	 * @param string $filename
	 */
	public static function export_table_xls ($data, $filename = 'export') {
		$file = api_get_path(SYS_ARCHIVE_PATH).uniqid('').'.xls';
		$handle = @fopen($file, 'a+');
		foreach ($data as $index => $row) {
			@fwrite($handle, implode("\t", $row)."\n");
		}
		@fclose($handle);
		DocumentManager :: file_send_for_download($file, true, $filename.'.xls');
		return false;
	}

	/**
	 * Export tabular data to XML-file
	 * @param array  Simple array of data to put in XML
	 * @param string Name of file to be given to the user
     * @param string Name of common tag to place each line in
     * @param string Name of the root element. A root element should always be given.
     * @param string Encoding in which the data is provided
	 */
	public static function export_table_xml ($data, $filename = 'export', $item_tagname = 'item', $wrapper_tagname = null, $encoding = null) {
		if (empty($encoding)) {
			$encoding = api_get_system_encoding();
		}
		$file = api_get_path(SYS_ARCHIVE_PATH).'/'.uniqid('').'.xml';
		$handle = fopen($file, 'a+');
		fwrite($handle, '<?xml version="1.0" encoding="'.$encoding.'"?>'."\n");
		if (!is_null($wrapper_tagname)) {
			fwrite($handle, "\t".'<'.$wrapper_tagname.'>'."\n");
		}
		foreach ($data as $index => $row) {
			fwrite($handle, '<'.$item_tagname.'>'."\n");
			foreach ($row as $key => $value) {
				fwrite($handle, "\t\t".'<'.$key.'>'.$value.'</'.$key.'>'."\n");
			}
			fwrite($handle, "\t".'</'.$item_tagname.'>'."\n");
		}
		if (!is_null($wrapper_tagname)) {
			fwrite($handle, '</'.$wrapper_tagname.'>'."\n");
		}
		fclose($handle);
		DocumentManager :: file_send_for_download($file, true, $filename.'.xml');
		return false;
	}

    /**
     * Export hierarchical tabular data to XML-file
     * @param array  Hierarchical array of data to put in XML, each element presenting a 'name' and a 'value' property
     * @param string Name of file to be given to the user
     * @param string Name of common tag to place each line in
     * @param string Name of the root element. A root element should always be given.
     * @param string Encoding in which the data is provided
     * @return void  Prompts the user for a file download
     */
    public static function export_complex_table_xml ($data, $filename = 'export', $wrapper_tagname, $encoding = 'ISO-8859-1') {
        $file = api_get_path(SYS_ARCHIVE_PATH).'/'.uniqid('').'.xml';
        $handle = fopen($file, 'a+');
        fwrite($handle, '<?xml version="1.0" encoding="'.$encoding.'"?>'."\n");

        if (!is_null($wrapper_tagname)) {
            fwrite($handle, '<'.$wrapper_tagname.'>');
        }
        $s = self::_export_complex_table_xml_helper($data);
        fwrite($handle,$s);
        if (!is_null($wrapper_tagname)) {
            fwrite($handle, '</'.$wrapper_tagname.'>'."\n");
        }
        fclose($handle);
        DocumentManager :: file_send_for_download($file, true, $filename.'.xml');
        return false;
    }

    /**
     * Helper for the hierarchical XML exporter
     * @param   array   Hierarhical array composed of elements of type ('name'=>'xyz','value'=>'...')
     * @param   int     Level of recursivity. Allows the XML to be finely presented
     * @return string   The XML string to be inserted into the root element
     */
    public static function _export_complex_table_xml_helper ($data, $level = 1) {
    	if (count($data)<1) { return '';}
        $string = '';
        foreach ($data as $index => $row) {
            $string .= "\n".str_repeat("\t",$level).'<'.$row['name'].'>';
            if (is_array($row['value'])) {
            	$string .= self::_export_complex_table_xml_helper($row['value'],$level+1)."\n";
                $string .= str_repeat("\t",$level).'</'.$row['name'].'>';
            } else {
                $string .= $row['value'];
                $string .= '</'.$row['name'].'>';
            }
        }
        return $string;
    }

}