-- #! mysql

-- # { economy
-- # { mail
-- # { mails
-- # { init
CREATE TABLE IF NOT EXISTS mails
(
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    title       TEXT    NOT NULL,
    content     TEXT    NOT NULL,
    send_xuid   TEXT    NOT NULL,
    author_xuid TEXT    NOT NULL,
    send_time   INTEGER NOT NULL,
    readed        INTEGER NOT NULL DEFAULT 0
);
-- # }

-- # { create
-- #    :title string
-- #    :content string
-- #    :send_xuid string
-- #    :author_xuid string
-- #    :send_time int
INSERT INTO mails (title, content, send_xuid, author_xuid, send_time)
VALUES (:title, :content, :send_xuid, :author_xuid, :send_time);
-- # }

-- # { seq
SHOW TABLE STATUS WHERE name = "mails";
-- # }

-- # { load
SELECT * FROM mails;
-- # }

-- # { update
-- #    :read int
-- #    :id int
UPDATE mails SET readed = :read WHERE id = :id;
-- # }

-- # { delete
-- #    :id int
DELETE FROM mails WHERE id = :id;
-- # }

-- # { drop
DROP TABLE IF EXISTS mails;
-- # }
-- # }
-- # }
-- # }