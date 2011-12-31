<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package   Modules
 * @category  Imagefly
 * @author    Fady Khalife
 * @uses      Image Module
 */
 
class ImageFly
{
    /**
     * @var  array       This modules config options
     */
    protected $config = NULL;
    
    /**
     * @var  string      Stores the path to the cache directory which is either whats set in the config "cache_dir"
     *                   or processed sub directories when the "mimic_sourcestructure" config option id set to TRUE
     */
    protected $cache_dir = NULL;
    
    /**
     * @var  object      Kohana image instance
     */
    protected $image = NULL;
    
    /**
     * @var  boolean     A flag for weither we should serve the default or cached image
     */
    protected $serve_default = FALSE;
    
    /**
     * @var  string      The source filepath and filename
     */
    protected $source_file = NULL;
    
    /**
     * @var  array       Stores the URL params in the following format
     *                   w = Width (int)
     *                   h = Height (int)
     *                   c = Crop (bool)
     */
    protected $url_params = array();
    
    /**
     * @var  string      Last modified Unix timestamp of the source file
     */
    protected $source_modified = NULL;
    
    /**
     * @var  string      The cached filename with path ($this->cache_dir)
     */
    protected $cached_file = NULL;
    
    /**
     * Constructorbot
     */
    public function __construct()
    {
        // Prevent unnecessary warnings on servers that are set to display E_STRICT errors, these will damage the image data.
        error_reporting(error_reporting() & ~E_STRICT);
        
        // Set the config
        $this->config = Kohana::$config->load('imagefly');
        
        // Try to create the cache directory if it does not exist
        $this->_create_cache_dir();
        
        // Parse and set the image modify params
        $this->_set_params();
        
        // Set the source file modified timestamp
        $this->source_modified = filemtime($this->source_file);
        
        // Try to create the mimic directory structure if required
        $this->_create_mimic_cache_dir();
        
        // Set the cached filepath with filename
        $this->cached_file = $this->cache_dir.$this->_encoded_filename();
        
        // Create a modified cache file or dont...
        if ( ! $this->_cached_exists() AND $this->_cached_required())
        {
            $this->_create_cached();
        }
        
        // Serve the image file
        $this->_serve_file();
    }
    
    /**
     * Try to create the config cache dir if required
     * Set $cache_dir
     */
    private function _create_cache_dir()
    {
        if( ! file_exists($this->config['cache_dir']))
        {
            try
            {
                mkdir($this->config['cache_dir'], 0755, TRUE);
            }
            catch(Exception $e)
            {
                throw new Kohana_Exception($e);
            }
        }
        
        // Set the cache dir
        $this->cache_dir = $this->config['cache_dir'];
    }
    
    /**
     * Try to create the mimic cache dir from the source path if required
     * Set $cache_dir
     */
    private function _create_mimic_cache_dir()
    {
        if ($this->config['mimic_sourcestructure'])
        {
            // Get the dir from the source file
            $mimic_dir = $this->config['cache_dir'].pathinfo($this->source_file, PATHINFO_DIRNAME);
            
            // Try to create if it does not exist
            if( ! file_exists($mimic_dir))
            {
                try
                {
                    mkdir($mimic_dir, 0755, TRUE);
                }
                catch(Exception $e)
                {
                    throw new Kohana_Exception($e);
                }
            }
            
            // Set the cache dir, with trailling slash
            $this->cache_dir = $mimic_dir.'/';
        }
    }
    
    /**
     * Sets the operations params from the url
     * w = Width (int)
     * h = Height (int)
     * c = Crop (bool)
     */
    private function _set_params()
    {
        // Get values from request
        $params = Request::current()->param('params');
        $filepath = Request::current()->param('imagepath');
        
        $this->image = Image::factory($filepath);
        
        // The parameters are separated by hyphens
        $raw_params	= explode('-', $params);
        
        // Set default param values
        $this->url_params['w'] = NULL;
        $this->url_params['h'] = NULL;
        $this->url_params['c'] = FALSE;
        
        // Update param values from passed values
        foreach ($raw_params as $raw_param)
        {
            $name = $raw_param[0];
            $value = substr($raw_param, 1, strlen($raw_param) - 1);
            if ($name == 'c')
            {
                $this->url_params[$name] = TRUE;
            }
            else
            {
                $this->url_params[$name] = $value;
            }
        }
        
        // Make width the height or vice versa if either is not passed
        if (empty($this->url_params['w']))
        {
            $this->url_params['w'] = $this->url_params['h'];
        }
        if (empty($this->url_params['h']))
        {
            $this->url_params['h'] = $this->url_params['w'];
        }
        
        // Must have at least a width or height
        if(empty($this->url_params['w']) AND empty($this->url_params['h']))
        {
            throw new Kohana_Exception('Invalid parameters, you must specify a width and/or height');
        }
  
        // Set the url filepath
        $this->source_file = $filepath;
    }
    
    /**
     * Checks if a physical version of the cached image exists
     * 
     * @return boolean
     */
    private function _cached_exists()
    {
        return file_exists($this->cached_file);
    }
    
    /**
     * Checks that the param dimensions are are lower then current image dimensions
     * 
     * @return boolean
     */
    private function _cached_required()
    {
        $image_info	= getimagesize($this->source_file);
        
        if (($this->url_params['w'] == $image_info[0]) AND ($this->url_params['h'] == $image_info[1]))
        {
            $this->serve_default = TRUE;
            return FALSE;
        }
        
        return TRUE;
    }
    
    /**
     * Returns a hash of the filepath and params plus last modified of source to be used as a unique filename
     * 
     * @return  string
     */
    private function _encoded_filename()
    {
        $ext = strtolower(pathinfo($this->source_file, PATHINFO_EXTENSION));
        $encode = md5($this->source_file.http_build_query($this->url_params));

        // Build the parts of the filename
        $encoded_name = $encode.'-'.$this->source_modified.'.'.$ext;

        return $encoded_name;
    }
    
    /**
     * Creates a cached cropped/resized version of the file
     */
    private function _create_cached()
    {
        if($this->url_params['c'])
        {
            // Resize to highest width or height with overflow on the larger side
            $this->image->resize($this->url_params['w'], $this->url_params['h'], Image::INVERSE);
            
            // Crop any overflow from the larger side
            $this->image->crop($this->url_params['w'], $this->url_params['h']);
        }
        else
        {
            // Just Resize
            $this->image->resize($this->url_params['w'], $this->url_params['h']);
        }
        
        // Save
        $this->image->save($this->cached_file);
    }
    
    /**
     * Create the image HTTP headers
     * 
     * @param  string     path to the file to server (either default or cached version)
     */
    private function _create_headers($file_data)
    {        
        // Create the required header vars
        $last_modified = gmdate('D, d M Y H:i:s', filemtime($file_data)).' GMT';
        $content_type = File::mime($file_data);
        $content_length = filesize($file_data);
        $expires = gmdate('D, d M Y H:i:s', (time() + $this->config['cache_expire'])).' GMT';
        $max_age = 'max-age='.$this->config['cache_expire'].', public';
        
        // Some required headers
        header("Last-Modified: $last_modified");
        header("Content-Type: $content_type");
        header("Content-Length: $content_length");

        // How long to hold in the browser cache
        header("Expires: $expires");

        /**
         * Public in the Cache-Control lets proxies know that it is okay to
         * cache this content. If this is being served over HTTPS, there may be
         * sensitive content and therefore should probably not be cached by
         * proxy servers.
         */
        header("Cache-Control: $max_age");
        
        // Set the 304 Not Modified if required
        $this->_modified_headers($last_modified);
        
        /**
         * The "Connection: close" header allows us to serve the file and let
         * the browser finish processing the script so we can do extra work
         * without making the user wait. This header must come last or the file
         * size will not properly work for images in the browser's cache
         */
        header("Connection: close");
    }
    
    /**
     * Rerurns 304 Not Modified HTTP headers if required and exits
     * 
     * @param  string  header formatted date
     */
    private function _modified_headers($last_modified)
    {  
        $modified_since = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
            ? stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE'])
            : FALSE;

        if ( ! $modified_since OR $modified_since != $last_modified)
            return;

        // Nothing has changed since their last request - serve a 304 and exit
        header('HTTP/1.1 304 Not Modified');
        header('Connection: close');
        exit();
    }
    
    /**
     * Decide which filesource we are using and serve
     */
    private function _serve_file()
    {
        // Set either the source or cache file as our datasource
        if ($this->serve_default)
        {
            $file_data = $this->source_file;
        }
        else
        {
            $file_data = $this->cached_file;
        }
        
        // Output the file
        $this->_output_file($file_data);
    }
    
    /**
     * Outputs the cached image file and exits
     * 
     * @param  string     path to the file to server (either default or cached version)
     */
    private function _output_file($file_data)
    {
        // Create the headers
        $this->_create_headers($file_data);
        
        // Get the file data
        $data = file_get_contents($file_data);

        // Send the image to the browser in bite-sized chunks
        $chunk_size	= 1024 * 8;
        $fp	= fopen('php://memory', 'r+b');

        // Process file data
        fwrite($fp, $data);
        rewind($fp);
        while ( ! feof($fp))
        {
            echo fread($fp, $chunk_size);
            flush();
        }
        fclose($fp);

        return $data;
        exit();
    }
}
