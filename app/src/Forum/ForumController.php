<?php

namespace Joah\Forum;

class ForumController implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectable,
        \Anax\MVC\TRedirectHelpers;
    
    protected $question;
    protected $answer;
    protected $comment;
    
    /**
     * Initialize the controller.
     *
     * @return void
     */
    public function initialize()
    {
        // load question model
        $this->question = new \Joah\Forum\Question();
        $this->question->setDI($this->di);

        // load answer model
        $this->answer = new \Joah\Forum\Answer();
        $this->answer->setDI($this->di);

        // load comment model
        $this->comment = new \Joah\Forum\Comment();
        $this->comment->setDI($this->di);

        // load tag model
        $this->tag = new \Joah\Forum\Tag();
        $this->tag->setDI($this->di);

        // // activate session
        // $this->di->session(); // Will load the session service which also starts the session

        // load CForm 
        $this->di->set('form', '\Mos\HTMLForm\CForm');
    }

    //set up database connection and add some example posts
    public function setupAction() {

        $this->views->addString('Databastabeller skapade');
        
        $this->theme->setTitle('Databastabeller skapade');
        // set to verbose to see what is happening
        // $this->db->setVerbose(); 

        // remove and create new question table
        $this->db->dropTableIfExists('question')->execute();
     
        $this->db->createTable(
            'question',
            [
                'id' => ['integer', 'primary key', 'not null', 'auto_increment'],
                'title' => ['varchar(80)'],
                'content' => ['text'],
                'user_id' => ['integer'],
                'timestamp' => ['datetime'],
            ]
        )->execute();

        // remove and create new answer table
        $this->db->dropTableIfExists('answer')->execute();
     
        $this->db->createTable(
            'answer',
            [
                'id' => ['integer', 'primary key', 'not null', 'auto_increment'],
                'question_id' => ['integer'],
                'content' => ['text'],
                'user_id' => ['integer'],
                'timestamp' => ['datetime'],
            ]
        )->execute();

        // remove and create new comment table
        $this->db->dropTableIfExists('comment')->execute();
     
        $this->db->createTable(
            'comment',
            [
                'id' => ['integer', 'primary key', 'not null', 'auto_increment'],
                'question_id' => ['integer'],
                'answer_id' => ['integer'],
                'content' => ['text'],
                'user_id' => ['integer'],
                'timestamp' => ['datetime'],
            ]
        )->execute();
        
        // remove and create new tag table
        $this->db->dropTableIfExists('tag')->execute();
     
        $this->db->createTable(
            'tag',
            [
                'id' => ['integer', 'primary key', 'not null', 'auto_increment'],
                'tag_text' => ['varchar(80)'],
            ]
        )->execute();
        
        // remove and create new tag2question table
        $this->db->dropTableIfExists('tag2question')->execute();
     
        $this->db->createTable(
            'tag2question',
            [
                'id' => ['integer', 'primary key', 'not null', 'auto_increment'],
                'tag_id' => ['integer'],
                'question_id' => ['integer'],
            ]
        )->execute();

    }
    
    
    public function testAction($id = null) 
    {
        // test route
        // echo "Nedan följer en dump av session['user']<br>";
        echo "Nedan följer en dump av session<br>";
        
        // dump($this->session->get('user'));
        dump($_SESSION);
        // dump($this->di);
        
    }
    
    /**
     *  Add some test data
     *  
     *  @return void
     */
    
    public function addTestDataAction() {
        
        //$now = time();
        $now = date('Y-m-d H:i:s');
        
        $this->views->addString('Testdata inläst i databasen');
        
        $this->theme->setTitle('Testdata');
        // set to verbose to see what is happening
         // $this->db->setVerbose(); 
        
        // test data question
        $this->db->insert(
            'question',
            ['title', 'content', 'user_id',  'timestamp']
        );
     
        $this->db->execute([
            'testfråga1',
            'Det här är en **frågetext** till *testfråga1*',
            1,
            $now
        ]);

        
        // test data answer
        $this->db->insert(
            'answer',
            ['question_id', 'content', 'user_id',  'timestamp']
        );
        
        $this->db->execute([
            1,
            'Det här är en **svarstext1** kopplad till *testfråga1*',
            2,
            $now
        ]);
        
        $this->db->execute([
            1,
            'Det här är en **svarstext2** kopplad till *testfråga1*',
            1,
            $now
        ]);

        // test data comment 
        $this->db->insert(
            'comment',
            ['question_id', 'answer_id', 'content', 'user_id',  'timestamp']
        );
        
        // test data question comment 
        $this->db->execute([
            1,
            null,
            'Det här är en **kommentarstext1** kopplad till *testfråga1*',
            2,
            $now
        ]);
        
        // test data answer comment 
        $this->db->execute([
            1,
            1,
            'Det här är en **kommentarstext1** kopplad till svar1 på *fråga1*',
            1,
            $now
        ]);
        
        $this->db->execute([
            1,
            1,
            'Det här är en **kommentarstext2** kopplad till svar1 på *fråga1*',
            1,
            $now
        ]);

        $this->db->execute([
            1,
            2,
            'Det här är en **kommentarstext1** kopplad till svar2 på *fråga1*',
            2,
            $now
        ]);

        // test data tags 
        $this->db->insert(
            'tag',
            ['tag_text']
        );

        $this->db->execute([
            'inomhus'
        ]);

        $this->db->execute([
            'utomhus'
        ]);
        
        $this->db->execute([
            'sniglar'
        ]);
        
        $this->db->execute([
            'husdjur'
        ]);

        // test data tags2question 
        $this->db->insert(
            'tag2question',
            ['tag_id', 'question_id']
        );

        $this->db->execute([
            1,
            1
        ]);

        $this->db->execute([
            4,
            1
        ]);
        
    }
    
    /**
     *  view a question with answers and comments.
     */
    public function viewAction($id)
    {
        
        $this->theme->setTitle("Fråga");
        // $this->views->addString('Här kollar vi på en fråga');
        
        // a question, as an object
        $question = $this->question->find($id);

        // fetch tags
        $tags = $this->tag->fetchTags($id);
        
        // comments belonging to question as array of object
        $questionComments = $this->comment->findQuestionComments($id);
        
        // multiple answers as array of objects
        $answers = $this->answer->findAnswers($id);
        
        // comments belonging to answers as array of object
        $answerComments = $this->comment->findAnswerComments($id);
        
        // run content through markdown filter
        // question
        $question->content = $this->textFilter->doFilter($question->content, 'markdown');

        // questionComments
        foreach($questionComments as $comment) {
            $comment->content = $this->textFilter->doFilter($comment->content, 'markdown');
        }
        
        // answers
        foreach($answers as $answer) {
            $answer->content = $this->textFilter->doFilter($answer->content, 'markdown');
        }
        
        // answerComments
        foreach($answerComments as $comment) {
            $comment->content = $this->textFilter->doFilter($comment->content, 'markdown');
        }

        
        $this->views->add('forum/view-question', [
            'question' => $question,
            'tags' => $tags,
            'questionComments' => $questionComments,
            'answers' => $answers,
            'answerComments' => $answerComments
        ]);
        
        // $this->views->addString('Lite text i en sidebar. Eventuellt lägga en knapp för att skapa en egen fråga här?', 'sidebar');

    }
    
    
    /**
     *  Default action
     */
    public function indexAction()
    {        
        // show overview as default
        $this->overviewQuestionAction();
    }

    
    /**
     *  Display overview of questions
     *  
     *  @return void
     */
    public function overviewQuestionAction()
    {
        $all = $this->question->findAll();

        // markdown filter
        foreach($all as $question) {
            $question->content = $this->textFilter->doFilter($question->content, 'markdown');
        }
    
    
        $this->theme->setTitle('Översikt frågor');
        $this->views->add('forum/overview-question', [
            'title' => "Översikt frågor",
            'questions' => $all,
            
        ]);
    
    }
    
    
    /**
     * Create a new question
     *
     * @return void
     */
    public function newQuestionAction()
    {
        // check if logged in.
        if($this->users->isLoggedIn()) {
        // redirect to login page and return after successfull login
            $this->redirectTo($this->url->create("users/login") . "?url=" . $this->request->getRoute());
            die("Funktionen kräver inloggning");
        }
    
        $this->theme->setTitle('Ställ en fråga!');

        
        $form = $this->di->form->create([], [
            'title' => [
                'type'        => 'text',
                'label'       => 'Titel:',
                'required'    => true,
                'validation'  => ['not_empty'],
            ],
            'content' => [
                'type'        => 'textarea',
                'label'       => 'Frågetext:',
                'required'    => true,
                'validation'  => ['not_empty'],
            ],
            'tags' => [
                'type'        => 'text',
                'label'       => 'Taggar (ex: #inomhus #sniglar)',
                'validation'  => array('not_empty', 'custom_test' => array('message' => 'Please only use #, letters, numbers and -.', 'test' => 'return ctype_alnum(str_replace(array(" ","-","_","#"), "", $value));')),
            ],

            'spara' => [
                'type'      => 'submit',
                'callback'  => [$this, 'saveQuestion'],
            ],

        ]);
        
        // Check the status of the form
        $status = $form->check();
     
        // if form was submitted
        if ($status === true) {
            //$form->AddOUtput("<p><i>Sparat!</i></p>");
            $this->redirectTo("forum/view/{$this->question->id}");
        } 
        // If form could not be submitted.
        else if ($status === false) {
            // What to do when form could not be processed?
            $form->AddOutput("<p><i>Något gick fel.</i></p>");
            
            $form->saveInSession = true;
            
            $this->redirectTo();
        }
        
        // Print form
        $this->di->views->add('forum/page', [
        'title' => "Ställ din fråga!",
        'content' => $form->getHTML()
        ]);

    }
    
    
    /**
     *  callback used to Save question 
     */
    public function saveQuestion($form)
    {
        
        // fetch user id from session
        $user_session = $this->session->get('user');
        $user_id = $user_session['id'];
        
        // save question
        $res = $this->question->save([
        'id' => null ==! $form->Value('id') ? $form->Value('id') : null,
        'title' => $form->Value('title'),
        'content' => $form->Value('content'),
        'user_id' => $user_id,
        'timestamp' => date('Y-m-d H:i:s'),
        ]);
    
        // fetch question_id
        $question_id = null ==! $form->Value('id') ? $form->Value('id') : $this->db->lastInsertId();
    
        $this->saveTags($form, $question_id);
        
        // Add a check for saveTAgs. AND? multiplication 1*0 = 0?
        return $res;
    }

    
    /**
     *  callback used to save tag
     */
    public function saveTags($form, $question_id)
    {
        // save tags
        // should I put this code elsewhere?

        // clear old entries in tag2question
        $this->db->delete('tag2question', "question_id = $question_id");
        $this->db->execute();
        
        //get string of tag(s) from form
        $tagString = $form->value('tags');

        // turn string into array
        $tagArray = $this->tag->tagStringToArray($tagString);
        
        // check if tag already exists
        $sql = "SELECT * FROM tag WHERE tag_text LIKE ?";
            
        // loop through array of tags
        foreach ($tagArray as $tag) {
            
            // check if tag already exists
            $existingTags = $this->db->executeFetchAll($sql, [$tag]);
            if(!empty($existingTags[0])) {
                // tags already in table, store id
                $tagID = $existingTags[0]->id;
            }
            else {
                // if not already there
                // add tag to tag table
                $this->db->insert('tag', ['tag_text'] , [$tag]);
                $this->db->execute();
                // store id of tag
                $tagID = $this->db->lastInsertId();
            }
   
            // add tag_id and question_id to tag2question
            $this->db->insert('tag2question', ['tag_id' => $tagID, 'question_id' => $question_id]);
            $this->db->execute();
        }        
    }
    
    
    /**
     * Edit question
     *
     * @return void
     */
    public function editQuestionAction($id = null)
    {

        // check if logged in.
        if($this->users->isLoggedIn()) {
        // redirect to login page and return after successfull login
            $this->redirectTo($this->url->create("users/login") . "?url=" . $this->request->getRoute());
            
        }
        
    
        $this->theme->setTitle('Redigera fråga');
        // test
        // $this->session->set('user_id', 1);

        // fetch question
        $q = $this->question->find($id);

        // check if question was found
        if (empty($q)) {
            die("question_id = '$id' not found");
        }
        
        // check editing rights
        if(!($this->users->verifyLoggedInAs($this->question->user_id))) {
            die("Du saknar rättigheter för den här åtgärden.");
        }
        
        
        $form = $this->di->form->create([], [
            'id' => [
                'type'        => 'hidden',
                'value'       => $id,
                'label'       => 'id',
            ],

            'title' => [
                'type'        => 'text',
                'value'       => $this->question->title,
                'label'       => 'Titel:',
                'required'    => true,
                'validation'  => ['not_empty'],
            ],
            
            'content' => [
                'type'        => 'textarea',
                'value'       => $this->question->content,
                'label'       => 'Frågetext:',
                'required'    => true,
                'validation'  => ['not_empty'],
            ],
            
            'tags' => [
                'type'        => 'text',
                'value'       => $this->tag->tagsToString($id),
                'label'       => 'Taggar (ex: #inomhus #sniglar)',
            ],
            
            'spara' => [
                'type'      => 'submit',
                'callback'  => [$this, 'saveQuestion'],
            ],

        ]);
        
        // Check the status of the form
        $status = $form->check();
     
        // if form was submitted
        if ($status === true) {
            //$form->AddOUtput("<p><i>Sparat!</i></p>");
            $this->redirectTo("forum/view/{$this->question->id}");
        } 
        // If form could not be submitted.
        else if ($status === false) {
            // What to do when form could not be processed?
            $form->AddOutput("<p><i>Något gick fel.</i></p>");
            
            $form->saveInSession = true;
            
            $this->redirectTo();
        }
        
        // Print form
        $this->di->views->add('forum/page', [
        'title' => "Redigera din fråga",
        'content' => $form->getHTML()
        ]);

    }
    

    /**
     * Delete question
     *
     * @return void
     */
    public function deleteQuestionAction($id)
    {
        // delete question
        $this->question->delete($id);
        // todo. delete comments and answers
        
        $this->redirectTo("forum/overview-question");
    }

    
    /**
     *  Display overview of tags
     *  
     *  @return void
     */
    public function overviewTagAction()
    {
        $this->theme->setTitle('Översikt taggar');

        // fetch counted tags
        $tags = $this->tag->countTags();
            
        $this->theme->setTitle('Översikt taggar');
        $this->views->add('forum/overview-tag', [
            'title' => "Översikt taggar",
            'tags' => $tags,
            
        ]);
    
    }

    /**
     *  view question associated with an tag
     */
    public function viewTagAction($tag_id)
    {
        $this->theme->setTitle('Tagg');
        $questions = $this->tag->findQuestionByTag($tag_id);

        // questionComments
        foreach($questions as $question) {
            $question->content = $this->textFilter->doFilter($question->content, 'markdown');
        };
        
        
        $this->views->add('forum/view-question-by-tag', [
            'title' => "Frågor med tagg #{$this->tag->find($tag_id)->tag_text}",
            'questions' => $questions
        ]);
        
        // $this->views->addString('Lite text i en sidebar. Här kanske jag kan lägga in en lista på populära taggar?', 'sidebar');

    }

    /**
     *  Fetch user contributions. Questions, Answers, and comments
     */
    public function fetchUserContribution($user_id)
    {
        // creates objects
        $this->initialize();
        
        $questions = $this->question->findQuestionByUser($user_id);
        $answers = $this->answer->findAnswerByUser($user_id);
        $comments = $this->comment->findCommentByUser($user_id);
        
        // makdown filter
        foreach($answers as $answer) {
            $answer->content = $this->textFilter->doFilter($answer->content, 'markdown');
        }
        foreach($comments as $comment) {
            $comment->content = $this->textFilter->doFilter($comment->content, 'markdown');
        }
        
        
        return ['questions' => $questions, 'answers' => $answers, 'comments' => $comments];
        
    }
    
    
    /**
     * Create an answer
     *
     * @return void
     */
    public function answerAction($question_id = null)
    {
        
        // check if logged in.
        if($this->users->isLoggedIn()) {
            // redirect to login page and return after successfull login
            $this->redirectTo($this->url->create("users/login") . "?url=" . $this->request->getRoute());
            die("Funktionen kräver inloggning");
        }
        
        $this->theme->setTitle('Besvara en fråga');

        // fetch question
        $q = $this->question->find($question_id);

        // check if question was found
        if (empty($q)) {
            die("question_id ='$question_id' not found");
        }
        
        
        $form = $this->di->form->create([], [
            'question_id' => [
                'type' => 'hidden',
                'value'       => $question_id,
                'label'       => 'question_id',
            ],
            'content' => [    
                'type'        => 'textarea',
                'label'       => 'Svarstext:',
                'required'    => true,
                'validation'  => ['not_empty'],
            ],
            'spara' => [
                'type'      => 'submit',
                'callback'  => [$this, 'saveAnswer'],
            ],

        ]);
        
        // Check the status of the form
        $status = $form->check();
     
        // if form was submitted
        if ($status === true) {
            //$form->AddOUtput("<p><i>Sparat!</i></p>");
            $this->redirectTo("forum/view/{$this->question->id}");
        } 
        // If form could not be submitted.
        else if ($status === false) {
            // What to do when form could not be processed?
            $form->AddOutput("<p><i>Något gick fel.</i></p>");
            
            $form->saveInSession = true;
            
            $this->redirectTo();
        }
        
        // Print form
        $this->di->views->add('forum/page', [
        'title' => "Besvara en fråga",
        'content' => $form->getHTML()
        ]);

    }
    
    
    /**
     *  callback used to Save answer 
     */
    public function saveAnswer($form)
    {
        // fetch user id from session
        $user_session = $this->session->get('user');
        $user_id = $user_session['id'];
        
        // save question
        $res = $this->answer->save([
        'id' => null ==! $form->Value('id') ? $form->Value('id') : null,
        'question_id' => $form->Value('question_id'),
        'content' => $form->Value('content'),
        'user_id' => $user_id,
        'timestamp' => date('Y-m-d H:i:s'),
        ]);
    
        return $res;
    }
    
    
    /**
     * Edit answer
     *
     * @return void
     */
    public function editAnswerAction($answer_id = null)
    {

        $this->theme->setTitle('Redigera svar');

        // fetch question
        $a = $this->answer->find($answer_id);

        // check if answer was found
        if (empty($a)) {
            die("answer_id = '$answer_id' not found");
        }

        // check editing rights
        if(!($this->users->verifyLoggedInAs($this->answer->user_id))) {
            die("Du saknar rättigheter för den här åtgärden.");
        }      
        
        
        $form = $this->di->form->create([], [
            'id' => [
                'type'        => 'hidden',
                'value'       => $answer_id,
                'label'       => 'id',
            ],
            'question_id' => [
                'type' => 'hidden',
                'value'       => $this->answer->question_id,
                'label'       => 'question_id',
            ],
            'content' => [
                'type'        => 'textarea',
                'value'       => $this->answer->content,
                'label'       => 'Svarstext:',
                'required'    => true,
                'validation'  => ['not_empty'],
            ],
            'spara' => [
                'type'      => 'submit',
                'callback'  => [$this, 'saveAnswer'],
            ],

        ]);
        
        // Check the status of the form
        $status = $form->check();
     
        // if form was submitted
        if ($status === true) {
            //$form->AddOUtput("<p><i>Sparat!</i></p>");
            $this->redirectTo("forum/view/{$this->answer->question_id}");
        } 
        // If form could not be submitted.
        else if ($status === false) {
            // What to do when form could not be processed?
            $form->AddOutput("<p><i>Något gick fel.</i></p>");
            
            $form->saveInSession = true;
            
            $this->redirectTo();
        }
        
        // Print form
        $this->di->views->add('forum/page', [
        'title' => "Redigera ditt svar",
        'content' => $form->getHTML()
        ]);

    }

    
    /**
     * Create a comment
     *
     * @return void
     */
    public function commentAction($question_id = null, $answer_id = null)
    {
        // check if logged in.
        if($this->users->isLoggedIn()) {
            // redirect to login page and return after successfull login
            $this->redirectTo($this->url->create("users/login") . "?url=" . $this->request->getRoute());
            die("Funktionen kräver inloggning");
        }
    
        $this->theme->setTitle('Kommentera');

        // fetch question
        $q = $this->question->find($question_id);
        // fetch answer
        $a = $this->answer->find($answer_id);

        // check if question was found
        if (empty($q)) {
            die("question_id ='$question_id' not found");
        }
        // check if answer_id matches question_id
        if (!empty($a) AND $question_id !== $a->question_id) {
            die("question_id and answer_id doesn't match");
        }
        
        
        
        $form = $this->di->form->create([], [
            'question_id' => [
                'type' => 'hidden',
                'value'       => $question_id,
                'label'       => 'question_id',
            ],
            'answer_id' => [
                'type' => 'hidden',
                'value'       => $answer_id,
                'label'       => 'answer_id',
            ],
            'content' => [    
                'type'        => 'textarea',
                'label'       => 'Kommentarstext:',
                'required'    => true,
                'validation'  => ['not_empty'],
            ],
            'spara' => [
                'type'      => 'submit',
                'callback'  => [$this, 'saveComment'],
            ],

        ]);
        
        // Check the status of the form
        $status = $form->check();
     
        // if form was submitted
        if ($status === true) {
            //$form->AddOUtput("<p><i>Sparat!</i></p>");
            $this->redirectTo("forum/view/{$this->question->id}");
        } 
        // If form could not be submitted.
        else if ($status === false) {
            // What to do when form could not be processed?
            $form->AddOutput("<p><i>Något gick fel.</i></p>");
            
            $form->saveInSession = true;
            
            $this->redirectTo();
        }
        
        // Print form
        $this->di->views->add('forum/page', [
        'title' => "Kommentera",
        'content' => $form->getHTML()
        ]);

    }
    
    
    /**
     *  callback used to Save comment 
     */
    public function saveComment($form)
    {
        // fetch user id from session
        $user_session = $this->session->get('user');
        $user_id = $user_session['id'];
        
        // keep numeric ids and turn empty ids into null
        $answer_id = is_numeric($form->Value('answer_id')) ? $form->Value('answer_id') : null;
        
        // save comment
        $res = $this->comment->save([
        'id' => null ==! $form->Value('id') ? $form->Value('id') : null,
        'question_id' => $form->Value('question_id'),
        'answer_id' => $answer_id,
        'content' => $form->Value('content'),
        'user_id' => $user_id,
        'timestamp' => date('Y-m-d H:i:s'),
        ]);
    
        return $res;
    }
    
    
    /**
     * Edit comment
     *
     * @return void
     */
    public function editCommentAction($comment_id = null)
    {
   
        $this->theme->setTitle('Redigera svar');

        // fetch comment
        $c = $this->comment->find($comment_id);

        // check if question was found
        if (empty($c)) {
            die("comment_id = '$comment_id' not found");
        }
        
        // check editing rights
        if(!($this->users->verifyLoggedInAs($this->comment->user_id))) {
            die("Du saknar rättigheter för den här åtgärden.");
        }
        
        
        $form = $this->di->form->create([], [
            'id' => [
                'type'        => 'hidden',
                'value'       => $comment_id,
                'label'       => 'id',
            ],
            'question_id' => [
                'type'        => 'hidden',
                'value'       => $this->comment->question_id,
                'label'       => 'question_id',
            ],
            'answer_id' => [
                'type'        => 'hidden',
                'value'       => $this->comment->answer_id,
                'label'       => 'answer_id',
            ],
            'content' => [
                'type'        => 'textarea',
                'value'       => $this->comment->content,
                'label'       => 'Kommentarstext:',
                'required'    => true,
                'validation'  => ['not_empty'],
            ],
            'spara' => [
                'type'      => 'submit',
                'callback'  => [$this, 'saveComment'],
            ],

        ]);
        
        // Check the status of the form
        $status = $form->check();
     
        // if form was submitted
        if ($status === true) {
            //$form->AddOUtput("<p><i>Sparat!</i></p>");
            $this->redirectTo("forum/view/{$this->comment->question_id}");
        } 
        // If form could not be submitted.
        else if ($status === false) {
            // What to do when form could not be processed?
            $form->AddOutput("<p><i>Något gick fel.</i></p>");
            
            $form->saveInSession = true;
            
            $this->redirectTo();
        }
        
        // Print form
        $this->di->views->add('forum/page', [
        'title' => "Redigera din kommentar",
        'content' => $form->getHTML()
        ]);

    }
    
    
    /**
     *  Display overview of latest questions
     *  
     *  @return void
     */
    public function latestQuestionsAction()
    {
        // fetch latest questions
        $all = $this->question->latestQuestions(5);

        // markdown filter
        foreach($all as $question) {
            $question->content = $this->textFilter->doFilter($question->content, 'markdown');
        }
    
        // add view
        $this->views->add('forum/latest-questions', [
            'title' => "Senaste frågorna",
            'questions' => $all,
        ]);
    
    }
    
    
    /**
     *  Display popular tags
     *  
     *  @return void
     */
    public function popularTagAction()
    {
        // fetch counted tags
        $tags = $this->tag->countTags();
            
        $this->views->add('forum/popular-tag', [
            'title' => "Populära taggar",
            'tags' => $tags,
        ]);
    }
        
    
    /**
     * List active users.
     *
     * @return void
     */
     public function activeUsersAction($num = 3)
    {
        $this->initialize();
     
        $all = $this->users->findAll();
        
        // get top three askers
        $askers = $this->question->activeUsers($num);
        $answerers = $this->answer->activeUsers($num);
        $commentators = $this->comment->activeUsers($num);

        // send to view
        $this->views->add('forum/active-users', [
            'askers' => $askers,
            'answerers' => $answerers,
            'commentators' => $commentators,
            'title' => "Aktiva användare",
        ]);
    }
 
}