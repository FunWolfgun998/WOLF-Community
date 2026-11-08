CREATE TABLE IF NOT EXISTS users(
	id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE articles ( 
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT, title VARCHAR(255) NOT NULL,
    introduction VARCHAR(512), 
    content TEXT NOT NULL,
    type ENUM('release', 'project', 'tutorial', 'discussion')
    NOT NULL DEFAULT 'discussion', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    -- Se un utente viene cancellato, si lasci null
    CONSTRAINT fk_article_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL );

CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE article_tags (
    article_id INT NOT NULL,
    tag_id INT NOT NULL,
    
    PRIMARY KEY (article_id, tag_id),
    
    CONSTRAINT fk_at_article FOREIGN KEY (article_id) 
        REFERENCES articles(id) ON DELETE CASCADE,
        
    CONSTRAINT fk_at_tag FOREIGN KEY (tag_id) 
        REFERENCES tags(id) ON DELETE CASCADE
);