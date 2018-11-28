<?php

class pluginRSSFeed extends Plugin {

	public function init()
	{
		// Fields and default values for the database of this plugin
		$this->dbFields = array(
			'numberOfItems'=>5,
			'maxWords'=>100
		);
	}

	// Method called on the settings of the plugin on the admin area
	public function form()
	{
		global $L;?>
<div class="card">
	<div class="card-header">
		<span class="badge badge-primary">
			<i class="oi oi-info"></i>
		</span>
		<?php echo $this->description() ?>
	</div>
	<div class="card-body">
		<div>
			<span class="mr-2">
				<?php echo $L->get('RSS URL') ?>
			</span>
			<a href="<?php echo DOMAIN_BASE ?>feed" target="_blank">
				<?php echo DOMAIN_BASE ?>feed
			</a>
		</div>
		<div class="row">
			<div class="form-group col-md-6 mb-2">
				<label>
					<?php echo $L->get('Amount of items') ?>
				</label>
				<input id="jsnumberOfItems" name="numberOfItems" type="text" value="<?php echo $this->getValue('numberOfItems') ?>" />
				<span class="text-muted">
					<?php echo $L->get('Amount of items to show on the feed') ?>
				</span>
			</div>
			<div class="form-group col-md-6 mb-2">
				<label>
					<?php echo $L->get('Limit the description to number of words') ?>
				</label>
				<input id="jsnumberOfItems" name="maxWords" type="text" value="<?php echo $this->getValue('maxWords') ?>" />
				<span class="text-muted">
					<?php echo $L->get('Maximum number of words in the description') ?>
				</span>
			</div>
		</div>
		<button type="submit" class="btn btn-primary mt-4">
			<?php echo $L->get('Save')?>
		</button>
	</div>
</div>
<?php
	}

	/**
	 * Get an excerpt
	 *
	 * @param string $content The content to be transformed
	 * @param int    $length  The number of words
	 * @param string $more    The text to be displayed at the end, if shortened
	 * @return string
	 */
	private function get_excerpt( $content, $maxwords = 100, $more = '...' ) {
		$excerpt = strip_tags( trim( $content ) );
		$words = str_word_count( $excerpt, 2 );
		if ( count( $words ) >  $maxwords ) {
			$words = array_slice( $words, 0,  $maxwords, true );
			end( $words );
			$position = key( $words ) + strlen( current( $words ) );
			$excerpt = substr( $excerpt, 0, $position ) . $more;
		}
		return $excerpt;
	}

	private function createXML()
	{
		global $site;
		global $pages;
		global $url;	


		// Amount of pages to show
		$numberOfItems = $this->getValue('numberOfItems');
		$maxWords =  $this->getValue('maxWords');
		// Page number the first one
		$pageNumber = 1;

		// Only published pages
		$onlyPublished = true;

		// Get the list of pages
		$list = $pages->getList($pageNumber, $numberOfItems, $onlyPublished);

		$xml =  '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
		$xml .= '<channel>';
		$xml .= '<title>'.$site->title().'</title>';
		$xml .= '<atom:link href="'.DOMAIN_BASE.'feed" rel="self" type="application/rss+xml" />';
		$xml .= '<link>'.$site->url().'</link>';
		$xml .= '<description>'.$site->description().'</description>';
		$xml .= '<lastBuildDate>'.date(DATE_RSS).'</lastBuildDate>';


		// Get keys of pages
		foreach ($list as $pageKey) {
			try {
				// Create the page object from the page key
				$page = new Page($pageKey);
				$xml .= '<item>';
				$xml .= '<title>'.$page->title().'</title>';
				$xml .= '<link>'.$page->permalink().'</link>';
				$xml .= '<description>'.Sanitize::html($this->get_excerpt( $page->contentBreak(), $maxWords)).'</description>';
				if(!empty($page->category())){
					$xml .= '<category><![CDATA['.Sanitize::html($page->category()).']]></category>';
				}
				$xml .= '<pubDate>'.$page->date(DATE_RSS).'</pubDate>';
				$xml .= '<guid isPermaLink="false">'.$page->uuid().'</guid>';
				$xml .= '</item>';
			}
			catch (Exception $e) {
				// Continue
			}
		}
		$xml .= '</channel></rss>';

		// New DOM document
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		$doc->loadXML($xml);		
		return $doc->save($this->workspace().'feed.xml');
	}

	public function install($position=0)
	{
		parent::install($position);
		return $this->createXML();
	}

	public function post()
	{
		
		// Create Xml
		$this->createXML();
		
		// Call the method
		return parent::post();
		

	}

	public function afterPageCreate()
	{
		$this->createXML();
	}

	public function afterPageModify()
	{
		$this->createXML();
	}

	public function afterPageDelete()
	{
		$this->createXML();
	}

	public function siteHead()
	{
		return '<link rel="alternate" type="application/rss+xml" href="'.DOMAIN_BASE.'feed" title="RSS Feed">'.PHP_EOL;
	}

	public function beforeAll()
	{
		$webhook = 'feed';
		if ($this->webhook($webhook)) {
			// Send XML header
			header('Content-type: text/xml');
			$doc = new DOMDocument();

			// Load XML
			libxml_disable_entity_loader(false);
			$doc->load($this->workspace().'feed.xml');
			libxml_disable_entity_loader(true);

			// Print the XML
			echo $doc->saveXML();

			// Stop Bludit execution
			exit(0);
		}
	}
}