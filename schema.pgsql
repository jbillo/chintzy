--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- Name: comment_status; Type: TYPE; Schema: public; Owner: chintzy
--

CREATE TYPE comment_status AS ENUM (
    'new',
    'approved',
    'spam',
    'rejected'
);


ALTER TYPE public.comment_status OWNER TO chintzy;


--
-- Name: comments; Type: TABLE; Schema: public; Owner: chintzy; Tablespace:
--

CREATE TABLE comments (
    id integer NOT NULL,
    post_id integer NOT NULL,
    text text NOT NULL,
    created_on timestamp without time zone DEFAULT now() NOT NULL,
    updated_on timestamp without time zone DEFAULT now() NOT NULL,
    user_name character varying(64) NOT NULL,
    user_email character varying(255) NOT NULL,
    user_url character varying(255) NOT NULL,
    status comment_status DEFAULT 'new'::comment_status NOT NULL
);


ALTER TABLE public.comments OWNER TO chintzy;

--
-- Name: comments_id_seq; Type: SEQUENCE; Schema: public; Owner: chintzy
--

CREATE SEQUENCE comments_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.comments_id_seq OWNER TO chintzy;

--
-- Name: comments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: chintzy
--

ALTER SEQUENCE comments_id_seq OWNED BY comments.id;

--
-- Name: permissions; Type: TABLE; Schema: public; Owner: chintzy; Tablespace:
--

CREATE TABLE permissions (
    id integer NOT NULL,
    name character varying(48) NOT NULL
);


ALTER TABLE public.permissions OWNER TO chintzy;

--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: chintzy
--

CREATE SEQUENCE permissions_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.permissions_id_seq OWNER TO chintzy;

--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: chintzy
--

ALTER SEQUENCE permissions_id_seq OWNED BY permissions.id;


--
-- Name: posts; Type: TABLE; Schema: public; Owner: chintzy; Tablespace:
--

CREATE TABLE posts (
    id integer NOT NULL,
    title character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    text text NOT NULL,
    created_on timestamp without time zone DEFAULT now() NOT NULL,
    updated_on timestamp without time zone DEFAULT now() NOT NULL,
    page boolean DEFAULT false NOT NULL,
    parent_id integer DEFAULT 1 NOT NULL,
    display_in_nav boolean DEFAULT true NOT NULL
);


ALTER TABLE public.posts OWNER TO chintzy;

--
-- Name: post_id_seq; Type: SEQUENCE; Schema: public; Owner: chintzy
--

CREATE SEQUENCE post_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.post_id_seq OWNER TO chintzy;

--
-- Name: post_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: chintzy
--

ALTER SEQUENCE post_id_seq OWNED BY posts.id;

--
-- Name: role_permissions; Type: TABLE; Schema: public; Owner: chintzy; Tablespace:
--

CREATE TABLE role_permissions (
    id integer NOT NULL,
    role_id integer NOT NULL,
    permission_id integer NOT NULL
);


ALTER TABLE public.role_permissions OWNER TO chintzy;

--
-- Name: roles; Type: TABLE; Schema: public; Owner: chintzy; Tablespace:
--

CREATE TABLE roles (
    id integer NOT NULL,
    name character varying(32) NOT NULL
);


ALTER TABLE public.roles OWNER TO chintzy;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: chintzy
--

CREATE SEQUENCE roles_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.roles_id_seq OWNER TO chintzy;

--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: chintzy
--

ALTER SEQUENCE roles_id_seq OWNED BY roles.id;


--
-- Name: sitelog; Type: TABLE; Schema: public; Owner: chintzy; Tablespace:
--

CREATE TABLE sitelog (
    id bigint NOT NULL,
    url character varying(255) NOT NULL,
    message text NOT NULL,
    type character(4) DEFAULT 'INFO'::bpchar NOT NULL,
    "user" character varying(32) DEFAULT 'none'::character varying NOT NULL,
    created_on timestamp with time zone DEFAULT now() NOT NULL
);


ALTER TABLE public.sitelog OWNER TO chintzy;

--
-- Name: sitelog_id_seq; Type: SEQUENCE; Schema: public; Owner: chintzy
--

CREATE SEQUENCE sitelog_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.sitelog_id_seq OWNER TO chintzy;

--
-- Name: sitelog_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: chintzy
--

ALTER SEQUENCE sitelog_id_seq OWNED BY sitelog.id;

--
-- Name: user_cookies; Type: TABLE; Schema: public; Owner: chintzy; Tablespace:
--

CREATE TABLE user_cookies (
    id integer NOT NULL,
    user_id integer NOT NULL,
    hash character(64) NOT NULL,
    created_on timestamp with time zone DEFAULT now() NOT NULL,
    expires_on timestamp with time zone NOT NULL
);


ALTER TABLE public.user_cookies OWNER TO chintzy;

--
-- Name: user_cookies_id_seq; Type: SEQUENCE; Schema: public; Owner: chintzy
--

CREATE SEQUENCE user_cookies_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.user_cookies_id_seq OWNER TO chintzy;

--
-- Name: user_cookies_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: chintzy
--

ALTER SEQUENCE user_cookies_id_seq OWNED BY user_cookies.id;


--
-- Name: user_permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: chintzy
--

CREATE SEQUENCE user_permissions_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.user_permissions_id_seq OWNER TO chintzy;

--
-- Name: user_permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: chintzy
--

ALTER SEQUENCE user_permissions_id_seq OWNED BY role_permissions.id;


--
-- Name: user_recovery; Type: TABLE; Schema: public; Owner: chintzy; Tablespace:
--

CREATE TABLE user_recovery (
    id integer NOT NULL,
    user_id integer NOT NULL,
    created_on timestamp with time zone DEFAULT now() NOT NULL,
    key character(64) NOT NULL,
    sent_messages smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.user_recovery OWNER TO chintzy;

--
-- Name: user_recovery_id_seq; Type: SEQUENCE; Schema: public; Owner: chintzy
--

CREATE SEQUENCE user_recovery_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.user_recovery_id_seq OWNER TO chintzy;

--
-- Name: user_recovery_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: chintzy
--

ALTER SEQUENCE user_recovery_id_seq OWNED BY user_recovery.id;


--
-- Name: user_roles; Type: TABLE; Schema: public; Owner: chintzy; Tablespace:
--

CREATE TABLE user_roles (
    id integer NOT NULL,
    user_id integer NOT NULL,
    role_id integer NOT NULL
);


ALTER TABLE public.user_roles OWNER TO chintzy;

--
-- Name: user_roles_id_seq; Type: SEQUENCE; Schema: public; Owner: chintzy
--

CREATE SEQUENCE user_roles_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.user_roles_id_seq OWNER TO chintzy;

--
-- Name: user_roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: chintzy
--

ALTER SEQUENCE user_roles_id_seq OWNED BY user_roles.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: chintzy; Tablespace:
--

CREATE TABLE users (
    id integer NOT NULL,
    email character varying(128) NOT NULL,
    password character(64) NOT NULL,
    created_on timestamp with time zone DEFAULT now() NOT NULL
);


ALTER TABLE public.users OWNER TO chintzy;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: chintzy
--

CREATE SEQUENCE users_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO chintzy;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: chintzy
--

ALTER SEQUENCE users_id_seq OWNED BY users.id;

--
-- Name: id; Type: DEFAULT; Schema: public; Owner: chintzy
--

ALTER TABLE comments ALTER COLUMN id SET DEFAULT nextval('comments_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: chintzy
--

ALTER TABLE permissions ALTER COLUMN id SET DEFAULT nextval('permissions_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: chintzy
--

ALTER TABLE posts ALTER COLUMN id SET DEFAULT nextval('post_id_seq'::regclass);

--
-- Name: id; Type: DEFAULT; Schema: public; Owner: chintzy
--

ALTER TABLE role_permissions ALTER COLUMN id SET DEFAULT nextval('user_permissions_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: chintzy
--

ALTER TABLE roles ALTER COLUMN id SET DEFAULT nextval('roles_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: chintzy
--

ALTER TABLE sitelog ALTER COLUMN id SET DEFAULT nextval('sitelog_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: chintzy
--

ALTER TABLE user_cookies ALTER COLUMN id SET DEFAULT nextval('user_cookies_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: chintzy
--

ALTER TABLE user_recovery ALTER COLUMN id SET DEFAULT nextval('user_recovery_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: chintzy
--

ALTER TABLE user_roles ALTER COLUMN id SET DEFAULT nextval('user_roles_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: chintzy
--

ALTER TABLE users ALTER COLUMN id SET DEFAULT nextval('users_id_seq'::regclass);


--
-- Name: pk_comment_id; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY comments
    ADD CONSTRAINT pk_comment_id PRIMARY KEY (id);


--
-- Name: pk_id; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY posts
    ADD CONSTRAINT pk_id PRIMARY KEY (id);


--
-- Name: pk_permissions_id; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT pk_permissions_id PRIMARY KEY (id);


--
-- Name: pk_role_id; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY roles
    ADD CONSTRAINT pk_role_id PRIMARY KEY (id);


--
-- Name: pk_sitelog_id; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY sitelog
    ADD CONSTRAINT pk_sitelog_id PRIMARY KEY (id);

--
-- Name: pk_user_cookies_id; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY user_cookies
    ADD CONSTRAINT pk_user_cookies_id PRIMARY KEY (id);


--
-- Name: pk_user_permissions_id; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY role_permissions
    ADD CONSTRAINT pk_user_permissions_id PRIMARY KEY (id);


--
-- Name: pk_user_recovery_id; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY user_recovery
    ADD CONSTRAINT pk_user_recovery_id PRIMARY KEY (id);


--
-- Name: pk_user_roles_id; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY user_roles
    ADD CONSTRAINT pk_user_roles_id PRIMARY KEY (id);


--
-- Name: pk_users_id; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY users
    ADD CONSTRAINT pk_users_id PRIMARY KEY (id);

--
-- Name: uk_slug; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY posts
    ADD CONSTRAINT uk_slug UNIQUE (slug);

--
-- Name: uk_user_cookies_userid; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY user_cookies
    ADD CONSTRAINT uk_user_cookies_userid UNIQUE (user_id);


--
-- Name: uk_user_recovery_userid; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY user_recovery
    ADD CONSTRAINT uk_user_recovery_userid UNIQUE (user_id);


--
-- Name: uk_users_email; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY users
    ADD CONSTRAINT uk_users_email UNIQUE (email);


--
-- Name: unique_permissions_name; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT unique_permissions_name UNIQUE (name);


--
-- Name: unique_role_name; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY roles
    ADD CONSTRAINT unique_role_name UNIQUE (name);


--
-- Name: unique_user_roles; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY user_roles
    ADD CONSTRAINT unique_user_roles UNIQUE (user_id, role_id);


--
-- Name: fki_comment_post_id; Type: INDEX; Schema: public; Owner: chintzy; Tablespace:
--

CREATE INDEX fki_comment_post_id ON comments USING btree (post_id);

--
-- Name: fki_user_cookies_userid; Type: INDEX; Schema: public; Owner: chintzy; Tablespace:
--

CREATE INDEX fki_user_cookies_userid ON user_cookies USING btree (user_id);


--
-- Name: fki_user_recovery_userid; Type: INDEX; Schema: public; Owner: chintzy; Tablespace:
--

CREATE UNIQUE INDEX fki_user_recovery_userid ON user_recovery USING btree (user_id);


--
-- Name: fki_user_roles_userid; Type: INDEX; Schema: public; Owner: chintzy; Tablespace:
--

CREATE INDEX fki_user_roles_userid ON user_roles USING btree (user_id);


--
-- Name: index_comment_post_id; Type: INDEX; Schema: public; Owner: chintzy; Tablespace:
--

CREATE INDEX index_comment_post_id ON comments USING btree (post_id);


--
-- Name: index_role_permissions_permissionid; Type: INDEX; Schema: public; Owner: chintzy; Tablespace:
--

CREATE INDEX index_role_permissions_permissionid ON role_permissions USING btree (permission_id);


--
-- Name: index_role_permissions_roleid; Type: INDEX; Schema: public; Owner: chintzy; Tablespace:
--

CREATE INDEX index_role_permissions_roleid ON role_permissions USING btree (role_id);


--
-- Name: index_sitelog_type; Type: INDEX; Schema: public; Owner: chintzy; Tablespace:
--

CREATE INDEX index_sitelog_type ON sitelog USING btree (type);


--
-- Name: index_user_roles_roleid; Type: INDEX; Schema: public; Owner: chintzy; Tablespace:
--

CREATE INDEX index_user_roles_roleid ON user_roles USING btree (role_id);

--
-- Name: fk_comment_post_id; Type: FK CONSTRAINT; Schema: public; Owner: chintzy
--

ALTER TABLE ONLY comments
    ADD CONSTRAINT fk_comment_post_id FOREIGN KEY (post_id) REFERENCES posts(id);

--
-- Name: fk_role_permissions_id; Type: FK CONSTRAINT; Schema: public; Owner: chintzy
--

ALTER TABLE ONLY role_permissions
    ADD CONSTRAINT fk_role_permissions_id FOREIGN KEY (role_id) REFERENCES roles(id);


--
-- Name: fk_role_permissions_permission_id; Type: FK CONSTRAINT; Schema: public; Owner: chintzy
--

ALTER TABLE ONLY role_permissions
    ADD CONSTRAINT fk_role_permissions_permission_id FOREIGN KEY (permission_id) REFERENCES permissions(id);


--
-- Name: fk_user_cookies_userid; Type: FK CONSTRAINT; Schema: public; Owner: chintzy
--

ALTER TABLE ONLY user_cookies
    ADD CONSTRAINT fk_user_cookies_userid FOREIGN KEY (user_id) REFERENCES users(id);


--
-- Name: fk_user_roles_roleid; Type: FK CONSTRAINT; Schema: public; Owner: chintzy
--

ALTER TABLE ONLY user_roles
    ADD CONSTRAINT fk_user_roles_roleid FOREIGN KEY (role_id) REFERENCES roles(id);


--
-- Name: fk_user_roles_userid; Type: FK CONSTRAINT; Schema: public; Owner: chintzy
--

ALTER TABLE ONLY user_roles
    ADD CONSTRAINT fk_user_roles_userid FOREIGN KEY (user_id) REFERENCES users(id);

--
-- Name: user_recovery_userid; Type: FK CONSTRAINT; Schema: public; Owner: chintzy
--

ALTER TABLE ONLY user_recovery
    ADD CONSTRAINT user_recovery_userid FOREIGN KEY (user_id) REFERENCES users(id);


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: chintzy; Tablespace:
--

CREATE TABLE sessions (
    session_id character varying(40) DEFAULT 0 NOT NULL,
    ip_address character varying(16) NOT NULL,
    user_agent character varying(120) NOT NULL,
    last_activity integer DEFAULT 0 NOT NULL,
    user_data text NOT NULL
);


ALTER TABLE public.sessions OWNER TO chintzy;

--
-- Name: sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: chintzy; Tablespace:
--

ALTER TABLE ONLY sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (session_id);


--
-- Name: idx_last_activity; Type: INDEX; Schema: public; Owner: chintzy; Tablespace:
--

CREATE INDEX idx_last_activity ON sessions USING btree (last_activity);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;



-- New constraint additions
CREATE UNIQUE INDEX "uk_user_permissions_role_permission" ON "public"."role_permissions" USING BTREE ("role_id","permission_id")

--
-- PostgreSQL database dump complete
--

