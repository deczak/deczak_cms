<?php

class schemeBlog extends CScheme
{
	public function
	__construct(bool $_applyConstraint = true)
	{
		parent::__construct('tb_page_blog_post');
		
		$this -> addColumn('post_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();
		$this -> addColumn('node_id', DB_COLUMN_TYPE_INT) -> setIndex('INDEX') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('post_page_color', DB_COLUMN_TYPE_STRING) -> setLength(30)-> setDefault('NULL');
		$this -> addColumn('post_text_color', DB_COLUMN_TYPE_STRING) -> setLength(30)-> setDefault('NULL');
		$this -> addColumn('post_background_mode', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('post_teasertext_mode', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('post_size_length_min', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('post_size_height', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('post_display_category', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		
		if($_applyConstraint)
			$this -> addConstraint('blogpost_node_id', 'node_id', 'tb_page', 'node_id', 'CASCADE', 'CASCADE');
	}
}
