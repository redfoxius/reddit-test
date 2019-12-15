<?php

class DbClient
{
    private $db = null;

    private $authorsList = null;

    /**
     * DbClient constructor.
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $host = $settings['host'] ?? 'localhost';
        $port = $settings['port'] ?? '3306';
        $name = $settings['name'] ?? 'test';
        $user = $settings['user'] ?? 'root';
        $pass = $settings['pass'] ?? '';

        try {
            $this->db = new PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name, $user, $pass);
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    /**
     * @param array $newData
     */
    public function insertData(array $newData) : void
    {
        foreach ($newData as $row) {
            $this->insertPost($row);
        }
    }

    /**
     * @param array $post
     */
    private function insertPost(array $post) : void
    {
        $author   = $post['author'] ?? '';
        $authorId = $this->getAuthorId($author);

        $sql  = "INSERT INTO `posts` (`headline`, `content`, `sub`, `url`, `author`) 
            VALUES (:headline, :content, :sub, :url, :author);";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'headline' => $post['headline'] ?? '',
            'content'  => $post['content'] ?? '',
            'sub'      => $post['sub'] ?? '',
            'url'      => $post['url'] ?? '',
            'author'   => $authorId,
        ]);
    }

    /**
     * @param string $author
     * @return int
     */
    private function getAuthorId(string $author) : int
    {
        if (empty($author)) {
            return 0;
        }
        $sql = 'SELECT * FROM `users` WHERE `username` = :user';
        $sth = $this->db->prepare($sql);
        $sth->execute([':user' => $author]);
        $user = $sth->fetch(PDO::FETCH_ASSOC);

        if (isset($user['id'])) {
            return intval($user['id']);
        } else {
            return $this->insertAuthor($author);
        }
    }

    /**
     * @param string $author
     * @return int
     */
    private function insertAuthor(string $author) : int
    {
        $sql  = "INSERT INTO `users` (`username`) VALUES (:username)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $author]);

        return $this->db->lastInsertId();
    }

    /**
     * @param int $id
     * @return array
     */
    private function getAuthor(int $id) : array
    {
        $sql = 'SELECT * FROM `users` WHERE `id` = :id';
        $sth = $this->db->prepare($sql);
        $sth->execute([':id' => $id]);
        $user = $sth->fetch(PDO::FETCH_ASSOC);

        return $user ?? [];
    }

    /**
     *
     */
    private function loadAuthors() : void
    {
        $sql = 'SELECT * FROM `users` WHERE 1 = 1';
        $sth = $this->db->prepare($sql);
        $sth->execute();
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);

        $cache = [];
        foreach ($results as $row) {
            $cache[intval($row['id'])] = $row;
        }
        $this->authorsList = $cache;
    }

    /**
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getPosts(int $limit = 25, int $page = 0) : array
    {
        $start = $page * $limit;

        if (!is_array($this->authorsList)) {
            $this->loadAuthors();
        }

        $sql = "SELECT * FROM `posts` WHERE 1 LIMIT {$start},{$limit}";
        $sth = $this->db->prepare($sql);
        $sth->execute();
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);

        $out = [];
        foreach ($results as $row) {
            $out[] = [
                'id'       => $row['id'],
                'headline' => $row['headline'],
                'content'  => $row['content'],
                'user'     => $this->authorsList[$row['author']],
            ];
        }

        return $out;
    }

    /**
     * @return int
     */
    public function getPostsCount() : int
    {
        $sql    = "SELECT COUNT(*) FROM `posts`";
        $sth    = $this->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_NUM);

        return intval($result[0] ?? 0);
    }

    /**
     * @param int $id
     */
    public function removePost(int $id) : void
    {
        $sql    = "DELETE FROM `posts` WHERE `id` = :id";
        $sth    = $this->db->prepare($sql);
        $sth->execute(['id' => $id]);
    }
}