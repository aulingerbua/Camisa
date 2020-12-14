<?php

class Textpage extends Registry
{

    private $page;

    /**
     *
     * @param string $page
     *            the name of the page all textpages belong to.
     */
    function __construct(string $page = NULL)
    {
        parent::setTable('textpages');
        parent::setUniqueField('id');
        parent::setOrderBy('cdate');
        $this->page = urldecode($page);
    }

    /**
     * Returns the page the textpages belong to.
     *
     * @return string page
     */
    public function getPage()
    {
        return $this->page;
    }

    protected function setPage($page)
    {
        $this->page = $page;
    }

    /**
     *
     * {@inheritdoc}
     * @see Registry::getOptions()
     */
    public function getOptions($which = NULL, $asArray = FALSE)
    {
        $which['page'] = $this->page;
        return parent::getOptions($which, $asArray);
    }

    /**
     * Displays the defaulf form for this class
     *
     * @param array $iniValues
     * @param string $chapter
     */
    public function showForm(array $iniValues = NULL)
    {
        $form[] = '<form action="" class="editor" method="post">';
        $form[] = "<div class='box-inside-form'>";
        $form[] = '<div>';
        $form[] = '<label for="title">Titel</label>';
        $form[] = '<input type="text" id="title" name="title" value="' . $iniValues['title'] . '">';
        $form[] = '<label for="category">Kategorie</label>';
        $k = parent::distinct("category");
        $category = '<input list="category" name="category" value="' . $iniValues['category'] . '">';
        $category .= '<datalist id="category">';
        while ($k) {
            $category .= '<option value="' . array_shift($k) . '">';
        }
        $form[] = $category . '</datalist>';
        $form[] = '</div>';
        $form[] = '<div>';
        if ($iniValues['type'] == 'multi') {
            $form[] = '<label for="chapter">Kapitel</label>';
            if (empty($iniValues['chapter'])) {
                $chap = 1;
            } else {
                $chap = $iniValues['chapter'];
            }
            $form[] = '<input type="number" id="chapter" name="chapter" size="3" value="' . $chap . '" min="1">';
        }
        unset($iniValues['type']);
        $form[] = '<label for="keywords">Schlagworte</label> <input type="text"
								id="keywords" name="keywords" value="' . $iniValues['keywords'] . '">';
        $form[] = '<label for="status">Status</label>';
        $form[] = '<select id="status"	name="status">
						<option value="public">sichtbar</option>
						<option value="draft">Entwurf</option>
						<option value="archive">Archiv</option>
					</select>';
        $form[] = '</div>';
        $form[] = '</div>';
        $form[] = '<textarea id="editor" name="text">' . $iniValues['text'] . '</textarea>';
        if ($iniValues) {
            $form[] = Registry::makeUpdateButton();
            $form[] = Registry::makeDeleteButton();
            $form[] = '<input type="hidden" id="id" name="id" value="' . $iniValues['id'] . '">';
            $form[] = '<input type="hidden" id="page" name="page" value="' . $iniValues['page'] . '">';
        } else {
            $form[] = Registry::makeInsertButton();
        }
        $form[] = '</form>';

        echo implode("\n", $form);
    }

    /**
     * Adds creation date and creator to $input before calling insert.
     *
     * {@inheritdoc}
     *
     * @see Registry::insert()
     */
    public function insert(array $input)
    {
        $input['cdate'] = date('Y-m-d G:i:s');
        $input['creator'] = $input['creator'] ?: current_user();
        $input['page'] = $input['page'] ?: $this->page;
        if (! $input['page']) {
            throw new Exception("To insert a text page the page must be specified.");
            exit(1);
        }
        parent::insert($input);
    }

    public function update(array $input, $which)
    {
        /*
         * add change date and
         * changer to $input before
         * calling update
         */
        $input['adate'] = date('Y-m-d G:i:s');
        $input['changer'] = $input['changer'] ?: current_user();
        parent::update($input, $which);
    }

    public function retrieveByPage($textonly = FALSE)
    {

        /*
         * get content from textpages by page name
         * if textonly is TRUE only the text and
         * title are received.
         */
        if ($textonly) {
            $data = self::retrieve([
                'page' => $this->page,
                'status' => 'public'
            ], NULL, 'title', 'text');
        } else {
            $data = self::retrieve([
                'page' => $this->page,
                'status' => 'public'
            ]);
        }

        return $data;
    }

    public function retrieveByChapter($chapter = NULL, $textonly = FALSE)
    {

        /*
         * get content from textpages by page name
         * and chapter.
         * if textonly is TRUE only the text and
         * title are received.
         */
        if (! $chapter)
            return NULL;
        if ($textonly) {
            $data = self::retrieve([
                'page' => $this->page,
                'chapter' => $chapter,
                'status' => 'public'
            ], NULL, 'title', 'text');
        } else {
            $data = self::retrieve([
                'page' => $this->page,
                'chapter' => $chapter,
                'status' => 'public'
            ]);
        }

        return $data;
    }

    public function retrieveByCat($cat, $page = NULL, $textonly = FALSE)
    {

        /*
         * get content from textpages by category name
         * if textonly is TRUE only the text and
         * title are received.
         */
        $selection = [
            category => $cat,
            status => 'public'
        ];
        if (! is_null($page)) {
            $selection['page'] = $page;
        }

        if ($textonly) {
            $data = self::retrieve($selection, NULL, 'title', 'text');
        } else {
            $data = self::retrieve($selection);
        }

        return $data;
    }

    public function retrieveByTitle($title, $page = NULL, $textonly = FALSE)
    {

        /*
         * get content from textpages by title
         * if textonly is TRUE only the text and
         * title are received.
         */
        $selection = [
            title => $title,
            status => 'public'
        ];
        if (! is_null($page)) {
            $selection['page'] = $page;
        }

        if ($textonly) {
            $data = self::retrieve($selection, NULL, 'title', 'text');
        } else {
            $data = self::retrieve($selection);
        }

        return $data;
    }

    /**
     * Gets content from textpages by title
     * if textonly is TRUE only the text and
     * title are received.
     *
     * @param string $title
     * @param string $cat
     * @param string $page
     * @param bool $textonly
     * @return array the data array
     */
    public function retrieveByCatAndTitle(string $title, string $cat, string $page = NULL, bool $textonly = FALSE)
    {
        $selection = [
            'title' => $title,
            'category' => $cat,
            'status' => 'public'
        ];
        if (! is_null($page)) {
            $selection['page'] = $page;
        }

        if ($textonly) {
            $data = self::retrieve($selection, NULL, 'title', 'text');
        } else {
            $data = self::retrieve($selection);
        }

        return $data;
    }

    static function hasCategories($page)
    {
        /*
         * check whether a page has content from
         * one or more categories
         */
        global $db;

        $qry = "SELECT DISTINCT category FROM textpages";

        if (! empty($page)) {
            $qry .= " WHERE page = '$page'";
        }

        // echo $qry;
        if ($result = $db->query($qry)) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row['category'];
            }
        } else {
            echo "Fehler in hasCategories! " . $db->error;
        }
        // var_dump($data);
        return $data;
    }

    public function countChapter()
    {
        /*
         * count the number of chapters
         * returns null for zero chapters
         */
        global $db;

        // page must be defined
        If (empty($this->page)) {
            die("Fehler in countChapter: keine Seite definiert.");
        }

        $qry = "SELECT chapter FROM $this->table WHERE page = '$page'";

        // echo $qry;
        if ($result = $db->query($qry)) {
            while ($row = $result->fetch_row()) {
                $nCh[] = $row[0];
            }
        } else {
            echo "Fehler in countChapter! " . $db->error;
        }

        if (empty($nCh)) {
            return null;
        } else if (max($nCh) == 0) {
            return null;
        } else {
            return count($nCh);
        }
    }

    /**
     * Creates a list of titles
     *
     * @param string $page
     *            the page name
     * @return array the list of titles belonging to page.
     */
    public function titleList()
    {
        global $db;

        $qry = "SELECT title FROM $this->table";

        if (! empty($this->page)) {
            $qry .= " WHERE page = '$this->page'";
        }

        if ($this->orderby) {
            $qry .= " ORDER BY $this->orderby";
        }

        // echo $qry;
        if ($result = $db->query($qry)) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row['title'];
            }
        } else {
            echo "Fehler in titleList! " . $db->error;
        }

        return $data;
    }

    public function getPageModule()
    {
        $db = db_init();
        $qry = "select module from templates join pages on (pages.template=templates.template) where name='" . $this->page . "';";
        $res = self::executeQuery($qry);
        return $res[0]['module'];
    }

    public function chapterLinkList()
    {
        // $db = db_init ();
        // Liste der Events ausgeben
        self::setOrderBy("chapter");
        $data = self::retrieve([
            'page' => $this->page
        ], NULL, 'title', 'chapter');

        if ($data) {
            echo "<ul>";
            foreach ($data as $d) {
                $current = set_current("chapter", $d['chapter']);
                $list = "<li>";
                $list .= "<a href=?tool=page&page=" . urlencode($this->page);
                $list .= "&chapter=" . $d["chapter"];
                $list .= " $current>";
                $list .= $d['title'] . " --> Kapitel " . $d["chapter"] . "</a>";
                $list .= "</li>";
                $list .= "<li>";
                echo $list;
            }
            $lnew = "<a href=?tool=page&page=" . urlencode($this->page);
            $lnew .= ">Neu</a></li>";
            echo "$lnew</ul>\n";
        }
    }

    /**
     * Returns all unique keywords belonging to a page.
     *
     * @return array keywords
     */
    public function getKeywords()
    {
        $qry = "SELECT keywords FROM $this->table WHERE page='$this->page';";
        $data = self::executeQuery($qry);
        foreach ($data as $k) {
            $keys = explode(",", $k['keywords']);
            foreach ($keys as $k)
                $keywords[] = trim($k);
        }

        return (array_unique($keywords));
    }
}