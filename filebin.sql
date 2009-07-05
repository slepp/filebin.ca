SET client_encoding = 'UTF8';

CREATE TABLE creators (
    creator_id serial NOT NULL,
    remote inet,
    openid text
);

CREATE TABLE filebin (
    file_id serial NOT NULL,
    tag character varying(32) NOT NULL,
    path character varying(512) NOT NULL,
    name character varying(512) NOT NULL,
    size integer NOT NULL,
    content_type character varying(64) NOT NULL,
    created timestamp with time zone DEFAULT now() NOT NULL,
    active boolean DEFAULT true NOT NULL,
    creator integer,
    creations integer DEFAULT 1,
    expires interval,
    "valid" boolean
);

CREATE TABLE "statistics" (
    file_id integer NOT NULL,
    hits integer DEFAULT 0 NOT NULL,
    last_hit timestamp with time zone
);

CREATE TABLE upload_tracking (
    upload_id character varying(64),
    file_id integer NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    error boolean,
    error_message text
);

ALTER TABLE ONLY creators
    ADD CONSTRAINT creators_pkey PRIMARY KEY (creator_id);
ALTER TABLE ONLY filebin
    ADD CONSTRAINT filebin_pkey PRIMARY KEY (file_id);
ALTER TABLE ONLY filebin
    ADD CONSTRAINT filebin_tag_key UNIQUE (tag);
CREATE UNIQUE INDEX creators_openid_idx ON creators USING btree (openid);
CREATE UNIQUE INDEX creators_remote_idx ON creators USING btree (remote);
CREATE INDEX filebin_path_idx ON filebin USING btree (path);
ALTER TABLE ONLY filebin
    ADD CONSTRAINT filebin_creator_fkey FOREIGN KEY (creator) REFERENCES creators(creator_id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE ONLY "statistics"
    ADD CONSTRAINT statistics_file_id_fkey FOREIGN KEY (file_id) REFERENCES filebin(file_id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY upload_tracking
    ADD CONSTRAINT upload_tracking_file_id_fkey FOREIGN KEY (file_id) REFERENCES filebin(file_id);
