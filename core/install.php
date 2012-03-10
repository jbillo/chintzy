<?php
define('NO_CACHE', true);
require_once 'includes/init.php';
$t->require_ssl();
$t->require_permission('root');

// Check if installer should be activated
if (file_exists("{$BASE}/db.inc.php")) {
    l("Attempted access to installer, but database configuration file already exists", "ERRR");
    $t->redirect("");
    exit;
}

// Otherwise, allow installer activation.
$t->display('install/install.tmpl.php');

/**
CREATE TABLE posts (
    id integer NOT NULL,
    title character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    text text NOT NULL,
    created_on timestamp without time zone DEFAULT now() NOT NULL,
    updated_on timestamp without time zone DEFAULT now() NOT NULL,
    page boolean DEFAULT false NOT NULL,
    parent_id integer DEFAULT 1 NOT NULL
);

--
-- Name: test_id_seq; Type: SEQUENCE; Schema: public; Owner: edgelink
--

CREATE SEQUENCE test_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.test_id_seq OWNER TO edgelink;

--
-- Name: test_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: edgelink
--

ALTER SEQUENCE test_id_seq OWNED BY posts.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: edgelink
--

ALTER TABLE posts ALTER COLUMN id SET DEFAULT nextval('test_id_seq'::regclass);


--
-- Name: pk_id; Type: CONSTRAINT; Schema: public; Owner: edgelink; Tablespace:
--

ALTER TABLE ONLY posts
    ADD CONSTRAINT pk_id PRIMARY KEY (id);


--
-- Name: uk_slug; Type: CONSTRAINT; Schema: public; Owner: edgelink; Tablespace:
--

ALTER TABLE ONLY posts
    ADD CONSTRAINT uk_slug UNIQUE (slug);



 */