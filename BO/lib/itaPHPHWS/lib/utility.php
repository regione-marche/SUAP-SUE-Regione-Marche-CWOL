<?php
// 	[-]CCYY-MM-DDThh:mm:ss[Z|(+|-)hh:mm]
function soapDateTime($tm) {
	return date('c', $tm);
}

if(function_exists("strptime") == false) {
    function strptime($sDate, $sFormat) {
        $aResult = array (
            'tm_sec'   => 0,
            'tm_min'   => 0,
            'tm_hour'  => 0,
            'tm_mday'  => 1,
            'tm_mon'   => 0,
            'tm_year'  => 0,
            'tm_wday'  => 0,
            'tm_yday'  => 0,
            'unparsed' => $sDate,
        );
        
        while($sFormat != "") {
            $nIdxFound = strpos($sFormat, '%');
            if($nIdxFound === false) {
                $aResult['unparsed'] = ($sFormat == $sDate) ? "" : $sDate;
                break;
            }
            
            $sFormatBefore = substr($sFormat, 0, $nIdxFound);
            $sDateBefore   = substr($sDate,   0, $nIdxFound);
            
            if($sFormatBefore != $sDateBefore) break;
            
            $sFormat = substr($sFormat, $nIdxFound);
            $sDate   = substr($sDate,   $nIdxFound);
            
            $aResult['unparsed'] = $sDate;
            
            $sFormatCurrent = substr($sFormat, 0, 2);
            $sFormatAfter   = substr($sFormat, 2);
            
            $nValue = -1;
            $sDateAfter = "";
            
            switch($sFormatCurrent) {
                case '%S':
                    
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
                    
                    if(($nValue < 0) || ($nValue > 59)) return false;
                    
                    $aResult['tm_sec']  = $nValue;
                    break;
                
                // ----------
                case '%M':
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
                    
                    if(($nValue < 0) || ($nValue > 59)) return false;
                
                    $aResult['tm_min']  = $nValue;
                    break;
                
                // ----------
                case '%H':
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
                    
                    if(($nValue < 0) || ($nValue > 23)) return false;
                
                    $aResult['tm_hour']  = $nValue;
                    break;
                
                // ----------
                case '%d':
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
                    
                    if(($nValue < 1) || ($nValue > 31)) return false;
                
                    $aResult['tm_mday']  = $nValue;
                    break;
                
                // ----------
                case '%m':
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);
                    
                    if(($nValue < 1) || ($nValue > 12)) return false;
                
                    $aResult['tm_mon']  = ($nValue - 1);
                    break;
                
                // ----------
                case '%Y':
                    sscanf($sDate, "%4d%[^\\n]", $nValue, $sDateAfter);
                    
                    if($nValue < 1900) return false;
                
                    $aResult['tm_year']  = ($nValue - 1900);
                    break;
                
                // ----------
                default:
                    break 2;
                
            }
            
            $sFormat = $sFormatAfter;
            $sDate   = $sDateAfter;
            
            $aResult['unparsed'] = $sDate;
            
        }
        
        $nParsedDateTimestamp = mktime(	$aResult['tm_hour'], $aResult['tm_min'], $aResult['tm_sec'],
										$aResult['tm_mon'] + 1, $aResult['tm_mday'], $aResult['tm_year'] + 1900);

        if(($nParsedDateTimestamp === false) ||($nParsedDateTimestamp === -1)) return false;
        
        $aResult['tm_wday'] = (int) strftime("%w", $nParsedDateTimestamp);
        $aResult['tm_yday'] = (strftime("%j", $nParsedDateTimestamp) - 1);

        return $aResult;
    }
}

function parseISO8601DateTime($datetime) {
	$currentTime = time();
	$offset = date("Z", $currentTime);

	$matches = array();

	$dateString = '';

	if(strstr($datetime,'.')!==false) {
		$parts = explode('.', $datetime);
		$datetime = $parts[0];
	}
	
	if(preg_match('/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})([+-])(\d{2}):(\d{2})$/',
						$datetime, $matches) === 1) {
		$dateString = $matches[1];

		$customOffset = $matches[3] * 60 * 60;
		$customOffset += $matches[4] * 60;

		if($matches[2] == "+") {
			$customOffset = -1 * $customOffset;
		}

		$offset += $customOffset;
	}
	else if(preg_match('/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})Z$/',
				$datetime, $matches) === 1) {
		$dateString = $matches[1];
	}
	else if(preg_match('/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})$/',
				$datetime, $matches) === 1) {
		$dateString = $matches[1];
	}

	$datetimeArray = strptime($dateString, "%Y-%m-%dT%H:%M:%S%");

	$time = mktime($datetimeArray['tm_hour'],
					$datetimeArray['tm_min'],
					$datetimeArray['tm_sec'],
					$datetimeArray['tm_mon'] + 1,
					$datetimeArray['tm_mday'] ,
					$datetimeArray['tm_year'] + 1900);

	return $time + $offset;
} 
?>