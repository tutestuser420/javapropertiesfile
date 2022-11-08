<?php

/**
 * This function reads a java properties file and
 * returns the contents as an array
 */

/**
 * @param string $filePath the file path of the propeties file
 *
 * @return array
 */
function parse_java_properties_file(string $filePath): array
{
    $data = [];
    if (!file_exists($filePath)) {
        throw new Exception("The file does not exist in the path: " . $filePath); 
    }
    //open file for reading
    $file = fopen($filePath, "r");
    //read the file
    while (!feof($file)) {
        //get each line data
        $line = fgets($file);
        //remove the new line character
        $line = rtrim($line, PHP_EOL);
        //remove all whitespaces from the start
        $line = ltrim($line);
        //ignore comments and empty lines
        if (empty($line)
            || (!$isLineContinuing && strpos($line, "#") === 0)
            || (!$isLineContinuing && strpos($line, "!") === 0)
        ) {
            continue;
        }
        //find the Key separator position i.e. = or : or whitespace
        $separatorPosition = function () use ($line) {
            $lineLength = strlen($line);
            $charCount  = 0;
            $offset = 0;
            $separatorPos = 0;
            while (true) {
                $posEqualTo = strpos($line, '=', $offset);
                $posColumn  = strpos($line, ':', $offset);
                $posSpace   = strpos($line, ' ', $offset);
                if ($posEqualTo === false && $posColumn === false && $posSpace === false) {
                    break;
                }

                $separatorPos = $posEqualTo ?: $posColumn ?: $posSpace;
                if (substr($line, $separatorPos - 1, 1) !== "\\") {
                    break;
                }

                if ($charCount++ >= $lineLength) {
                    break;
                }
                $offset = $separatorPos + 1;
            }
            return $separatorPos;
        };
        if (!$isLineContinuing) {
            //Key separator is = or : or whitespace
            // $keySeparatorPos = strpos($line, '=')
            // ?: strpos($line, ':')
            // ?: strpos($line, ' ');
            $keySeparatorPos = $separatorPosition();
            //key is the string that is left to the =
            $key = trim(substr($line, 0, $keySeparatorPos));
            $key = str_replace("\\", "", $key);
            //value is the string that is
            $value = substr($line, $keySeparatorPos + 1);
            $value = trim(rtrim($value, "\\"));
        } else {
            $value .= trim(rtrim($line, "\\"));
        }
        //check if line ends with \ which means the line is continuing
        $isLineContinuing = substr($line, -1) === "\\";
        if (!$isLineContinuing && !empty($key)) {
            $data[$key] = $value;
        }
    }

    return $data;
}