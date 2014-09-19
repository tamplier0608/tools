<?php

namespace Csv;

/**
 * Tool for simplest work with *.csv files
 *
 * @package Csv
 * @author Serhii Kukunin <kukunin.sergey@gmail.com>
 * @version 1.0
 */
class File implements \Iterator, \Countable
{    
    /**
     * List of the defaults options
     *
     * @var array
     */
    private $defaultOptions = array(
        'separator' => ',',
        'text_delimiter' => '"',
        'length' => 0,
        'fields' => array(),
        'readmode' => 'r',
        'writemode' => 'w+',
        'header' => false
    );
    
    /**
     * Parser options
     *
     * @var array
     */
    private $options;
    
    /**
     * CSV data
     *
     * @var array
     */
    private $csv;
    
    /**
     * Resource of file with CSV data
     *
     * @var resource
     */
    private $file;
    
    /**
     * Path to file
     *
     * @var string
     */
    private $path;
    
    /**
     * Array with fields of header file if exists
     *
     * @var array
     */
    private $header = array();

    /**
     * Constructor
     * @param string $filePath
     * @param array $options
     * @return \Csv\File
     */
    public function __construct($filePath, $options = null) 
    {
        if (!is_null($options)) {
            $this->options = array_merge($this->defaultOptions, $options);
        } else {
            $this->options = $this->defaultOptions;
        }
        
        foreach ($this->options as $key => $value) {
            $this->$key = $value;
        }

        $this->path = $filePath;
        $this->csv = $this->parse();
        
        return $this;
    }
    
    /**
     * Parse CSV file and save it in array.
     * If set option header elements of the array will be StdObjects
     *
     * @return array Array of CSV data
     */
    protected function parse()
    {        
        $csv = array();
        
        if (file_exists($this->path)) {
            $mode = $this->readmode;
        } else {
            $mode = $this->writemode;
        }
        
        $this->open($this->path, $mode);
        
        if ($this->header) {
            $header = fgetcsv($this->file, $this->length, $this->separator);
            $this->header = $header;
            
            while ($row = fgetcsv($this->file, $this->length, $this->separator, $this->text_delimiter)) {
                $temp = array();
                foreach ($row as $key => $field) {
                    if (isset($header[$key]) && !empty($header[$key])) {
                        $name = strtolower(str_replace(' ', '_', $header[$key]));
                        $temp[$name] = $field;
                    }
                }
                $csv[] = (object)$temp;
            }            
        } else {        
            while ($row = fgetcsv($this->file, $this->length, $this->separator)) {
                $csv[] = $row;
            }
        }
        $this->close();
        
        return $csv;
    }
    
    /**
     * Set CSV data array
     *
     * @param type $csv
     * @return \Csv\File
     */
    public function setCSVData($csv)
    {
        $this->csv = $csv;
        return $this;
    }
    
    /**
     * Get CSV data array
     *
     * @return array
     */
    public function getCSVData()
    {
        return $this->csv;
    }
    
    /**
     * Open CSV file with current mode
     * @param string $path Path to file
     * @param string $mode File will be opened with this modificator
     * @throws \Exception 
     */
    protected function open($path, $mode)
    {
         $this->file = fopen($path, $mode);
         if (!$this->file) {
             throw new \Exception("Failed to open file '$path'");
         }
    }
    
    /**
     * Closes opened CSV file
     */
    protected function close()
    {
        fclose($this->file);
    }
    
    /**
     * Saves CSV data in file
     */
    public function save()
    {
        $this->open($this->path, $this->writemode);
        
        if ($this->header) {
            fputcsv($this->file, $this->header, $this->separator);
        }
        foreach ($this->csv as $key => $value) {
            fputcsv($this->file, array_values((array) $value), $this->separator, $this->text_delimiter);
        }
        
        $this->close();
    }
    
    /**
     * Get header of CSV file
     * @return array 
     */
    public function getHeader() 
    {
        return $this->header;
    }
    
    /**
     * Set header of CSV file
     * @param array $header
     * @return \Csv\File
     */
    public function setHeader($header) 
    {
        $this->header = $this->clean($header);
        return $this;
    }
  
    /**
     * @todo Function is not working
     * 
     * @param array $header
     * @return array 
     */
    public function clean($header)
    { 
        foreach ($header as $key => $field) {
            if (is_string($field) && mb_strlen(trim($field)) <= 0) {
                unset($this->header[$key]);
                continue;
            }
            $header[ $key ] = trim( $field, '' );
        }
        return $header;
    }
    
    /**
     * Set parser option
     * @param string $name
     * @param mixed $value
     * @return \CsvFile
     */
    public function setOption($name, $value) 
    {
        if (array_key_exists($name, $this->defaultOptions)) {
            $this->$name = $value;
        }
        
        return $this;
    }
    
    /**
     * Get parser option value
     * @param string $name
     * @return boolean 
     */
    public function getOption($name) 
    {
        if (array_key_exists($name, $this->defaultOptions)) {
            return $this->$name;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function current() {
        return current($this->csv);
    }

    /**
     * @return mixed
     */
    public function key() {
        return key($this->csv);
    }

    /**
     * @return mixed
     */
    public function next() {
        return next($this->csv);
    }

    /**
     * @return mixed
     */
    public function rewind() {
        return reset($this->csv);
    }

    /**
     * @return bool
     */
    public function valid() {
        $key = key($this->csv);
        
        return (!is_null($key) && false !== $key);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->csv);
    }
   
}
