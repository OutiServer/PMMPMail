-- #! sqlite

-- # { economy
-- # { mail
-- # { mails
-- # { init
CREATE TABLE IF NOT EXISTS mails
(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title       TEXT    NOT NULL,
    content     TEXT    NOT NULL,
    send_xuid   TEXT    NOT NULL,
    author_xuid TEXT    NOT NULL,
    send_time   INTEGER NOT NULL,
    read        INTEGER NOT NULL DEFAULT 0
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
SELECT seq FROM sqlite_sequence WHERE name = 'mails';
-- # }

-- # { load
SELECT * FROM mails;
-- # }

-- # { update
-- #    :read int
-- #    :id int
UPDATE mails SET read = :read WHERE id = :id;
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