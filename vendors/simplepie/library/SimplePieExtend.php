<?php

/**
 *
 */
class SimplePieExtend extends SimplePie {

	/**
	 *
	 * @var bool
	 */
	protected $isFeedUrl = true;

	/**
	 *
	 */
	public function __construct() {
		parent::__construct();

		//$this->isFeedUrl = false;
	}


	/**
	 * Fetch the data via SimplePie_File
	 *
	 * If the data is already cached, attempt to fetch it from there instead
	 * @param SimplePie_Cache|false $cache Cache handler, or false to not load from the cache
	 * @return array|true Returns true if the data was loaded from the cache, or an array of HTTP headers and sniffed type
	 */
	// サイトのURL指定時にこれが呼ばれる
	protected function fetch_data(&$cache)
	{
		// If it's enabled, use the cache
		if ($cache)
		{
			// Load the Cache
			$this->data = $cache->load();
			if (!empty($this->data))
			{
				// If the cache is for an outdated build of SimplePie
				if (!isset($this->data['build']) || $this->data['build'] !== SIMPLEPIE_BUILD)
				{
					$cache->unlink();
					$this->data = array();
				}
				// If we've hit a collision just rerun it with caching disabled
				elseif (isset($this->data['url']) && $this->data['url'] !== $this->feed_url)
				{
					$cache = false;
					$this->data = array();
				}
				// If we've got a non feed_url stored (if the page isn't actually a feed, or is a redirect) use that URL.
				elseif (isset($this->data['feed_url']))
				{
					// If the autodiscovery cache is still valid use it.
					if ($cache->mtime() + $this->autodiscovery_cache_duration > time())
					{
						// Do not need to do feed autodiscovery yet.
						if ($this->data['feed_url'] !== $this->data['url'])
						{
							//
							//echo "フィードではない";
$this->isFeedUrl = false;
							$this->set_feed_url($this->data['feed_url']);
							return $this->init();
						}

						$cache->unlink();
						$this->data = array();
					}
				}
				// Check if the cache has been updated
				elseif ($cache->mtime() + $this->cache_duration < time())
				{

					// If we have last-modified and/or etag set
					if (isset($this->data['headers']['last-modified']) || isset($this->data['headers']['etag']))
					{
						$headers = array(
								'Accept' => 'application/atom+xml, application/rss+xml, application/rdf+xml;q=0.9, application/xml;q=0.8, text/xml;q=0.8, text/html;q=0.7, unknown/unknown;q=0.1, application/unknown;q=0.1, */*;q=0.1',
						);
						if (isset($this->data['headers']['last-modified']))
						{
							$headers['if-modified-since'] = $this->data['headers']['last-modified'];
						}
						if (isset($this->data['headers']['etag']))
						{
							$headers['if-none-match'] = $this->data['headers']['etag'];
						}

						$file = $this->registry->create('File', array($this->feed_url, $this->timeout/10, 5, $headers, $this->useragent, $this->force_fsockopen));

						if ($file->success)
						{
							if ($file->status_code === 304)
							{
								$cache->touch();
								return true;
							}
						}
						else
						{
							unset($file);
						}
					}
				}
				// If the cache is still valid, just return true
				else
				{
					$this->raw_data = false;
					return true;
				}
			}
			// If the cache is empty, delete it
			else
			{
				$cache->unlink();
				$this->data = array();
			}
		}
		// If we don't already have the file (it'll only exist if we've opened it to check if the cache has been modified), open it.
		if (!isset($file))
		{

			if ($this->file instanceof SimplePie_File && $this->file->url === $this->feed_url)
			{
				$file =& $this->file;
			}
			else
			{
				$headers = array(
						'Accept' => 'application/atom+xml, application/rss+xml, application/rdf+xml;q=0.9, application/xml;q=0.8, text/xml;q=0.8, text/html;q=0.7, unknown/unknown;q=0.1, application/unknown;q=0.1, */*;q=0.1',
				);
				$file = $this->registry->create('File', array($this->feed_url, $this->timeout, 5, $headers, $this->useragent, $this->force_fsockopen));
			}

		}
		// If the file connection has an error, set SimplePie::error to that and quit
		if (!$file->success && !($file->method & SIMPLEPIE_FILE_SOURCE_REMOTE === 0 || ($file->status_code === 200 || $file->status_code > 206 && $file->status_code < 300)))
		{
			$this->error = $file->error;
			return !empty($this->data);
		}

		if (!$this->force_feed)
		{
			// Check if the supplied URL is a feed, if it isn't, look for it.
			$locate = $this->registry->create('Locator', array(&$file, $this->timeout, $this->useragent, $this->max_checked_feeds));

			if (!$locate->is_feed($file))
			{
				//echo "フィードではない";
$this->isFeedUrl = false;
				// We need to unset this so that if SimplePie::set_file() has been called that object is untouched
				unset($file);
				if (!($file = $locate->find($this->autodiscovery, $this->all_discovered_feeds)))
				{
					$this->error = "A feed could not be found at $this->feed_url. A feed with an invalid mime type may fall victim to this error, or " . SIMPLEPIE_NAME . " was unable to auto-discover it.. Use force_feed() if you are certain this URL is a real feed.";
					$this->registry->call('Misc', 'error', array($this->error, E_USER_NOTICE, __FILE__, __LINE__));
					return false;
				}
				if ($cache)
				{
					$this->data = array('url' => $this->feed_url, 'feed_url' => $file->url, 'build' => SIMPLEPIE_BUILD);
					if (!$cache->save($this))
					{
						trigger_error("$this->cache_location is not writeable. Make sure you've set the correct relative or absolute path, and that the location is server-writable.", E_USER_WARNING);
					}
					$cache = $this->registry->call('Cache', 'create', array($this->cache_location, call_user_func($this->cache_name_function, $file->url), 'spc'));
				}
				//echo $this->feed_url;

				$this->feed_url = $file->url;
				//echo $this->feed_url;

			}
			$locate = null;
		}

		$this->raw_data = $file->body;

		$headers = $file->headers;
		//debug($file->body);
		$sniffer = $this->registry->create('Content_Type_Sniffer', array(&$file));
		$sniffed = $sniffer->get_type();
		//debug($sniffed);
		return array($headers, $sniffed);
	}

	/**
	 *
	 */
	public function IsUrlFeedUrl () {
		return $this->isFeedUrl;
	}
}
