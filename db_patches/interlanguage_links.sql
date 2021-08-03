CREATE TABLE /*_*/interlanguage_links (
    -- page_id of the referring page
    ill_from int unsigned NOT NULL default 0,

    -- Language code of the target
    ill_lang varbinary(20) NOT NULL default '',

    -- Title of the target, including namespace
    ill_title varchar(255) binary NOT NULL default '',
    PRIMARY KEY (ill_from, ill_lang)
) /*$wgDBTableOptions*/;

-- Index for ApiQueryLangbacklinks
CREATE INDEX /*i*/ill_lang ON /*_*/interlanguage_links (ill_lang, ill_title);
