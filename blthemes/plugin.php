<?php

class pluginBlThemes extends Plugin
{


    public function install($position = 0)
    {
        parent::install($position);
        $this->createSearchJson();
    }


    public function afterPageCreate()
    {
        $this->ensureDescription();
        $this->createSearchJson();

    }

    public function afterPageModify()
    {
        $this->ensureDescription();
        $this->createSearchJson();

    }

    public function afterPageDelete()
    {
        $this->createSearchJson();
    }


    private function ensureDescription()
    {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $key = isset($_POST['key']) ? $_POST['key'] : '';
            if ($key) {
                $page = new Page($key);
                if (!$page->description()) {
                    $cont = str_replace('<', ' <', $page->content(false));
                    $cont = html_entity_decode($cont);
                    $description = Text::truncate(Text::removeHTMLTags($cont), 280);
                    $description = trim(preg_replace('/\s+/', ' ', $description));//remove repeated spaces
                    $item = array();
                    $item['key'] = $key;
                    $item['description'] = $description;
                    editPage($item);
                }

            }
        }

    }

    private function createSearchJson()
    {
        global $pages;

		// Get the list of published pages
        $list = $pages->getPublishedDB(false);
        $jsonFile = PATH_UPLOADS . 'search.json';
        if (file_exists($jsonFile)) @unlink($jsonFile);
        $json = new dbJSON($jsonFile, false);

        foreach ($list as $key => $page) {
            try {
                $item = array();
                $item['title'] = $page['title'];
                $item['description'] = $page['description'];
                $item['slug'] = $key;
                $json->db[] = $item;
            } catch (Exception $e) {
				// Continue
            }
        }
        $json->save();

    }


}