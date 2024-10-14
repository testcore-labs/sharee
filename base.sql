-- public.bans definition

-- Drop table

-- DROP TABLE public.bans;

CREATE TABLE public.bans (
	id serial4 NOT NULL,
	"identity" varchar(512) NOT NULL,
	reason varchar(512) NOT NULL,
	permanent int2 NOT NULL,
	offendingitem varchar(128) NOT NULL,
	length int8 NOT NULL,
	createdat int8 NOT NULL,
	CONSTRAINT bans_pkey PRIMARY KEY (id)
);


-- public."comments" definition

-- Drop table

-- DROP TABLE public."comments";

CREATE TABLE public."comments" (
	id serial4 NOT NULL,
	fileid varchar(128) NOT NULL,
	"comment" varchar(128) NOT NULL,
	"identity" varchar(512) NOT NULL,
	createdat int8 NOT NULL,
	CONSTRAINT comments_pkey PRIMARY KEY (id)
);


-- public.files definition

-- Drop table

-- DROP TABLE public.files;

CREATE TABLE public.files (
	id varchar(128) NOT NULL,
	file varchar(256) NOT NULL,
	description varchar(512) NOT NULL,
	filename varchar(128) NOT NULL,
	mimetype varchar(1024) NOT NULL,
	"size" int4 NOT NULL,
	"key" varchar(1024) NOT NULL,
	"identity" varchar(512) NOT NULL,
	privacy int2 NOT NULL,
	createdat int8 NOT NULL,
	downloads int4 DEFAULT 0 NOT NULL
);