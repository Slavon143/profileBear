<!-- template="muie-archive-list-style" -->
<!-- mla_shortcode_slug="muie-archive-list" -->
<style type='text/css'>
	#[+selector+] {
		margin: auto;
		width: 100%;
	}
	#[+selector+] .muie-archive-list-item {
		text-align: left;
	}
	#[+selector+] li.[+current_archive_class+] a {
		font-weight: bold;
		font-size:larger;
	}
</style>

<!-- template="muie-archive-dropdown-arguments-markup" -->
mla_shortcode_slug="muie-archive-list"

<!-- template="muie-archive-dropdown-open-markup" -->
<[+listtag+] name="[+listtag_name+]" id="[+listtag_id+]" class="[+listtag_class+]" [+listtag_attributes+]>

<!-- template="muie-archive-dropdown-item-markup" -->
	<[+itemtag+] id="[+item_id+]" class="[+item_class+]" [+item_attributes+] [+item_selected+] value="[+item_value+]">[+item_label+]</[+itemtag+]>

<!-- template="muie-archive-dropdown-close-markup" -->
</[+listtag+]>

<!-- template="muie-archive-list-arguments-markup" -->
mla_shortcode_slug="muie-archive-list"

<!-- template="muie-archive-list-open-markup" -->
<[+listtag+] id="[+listtag_id+]" class="[+listtag_class+]" [+listtag_attributes+]>

<!-- template="muie-archive-list-item-markup" -->
	<[+itemtag+] id="[+item_id+]" class="[+item_class+]" [+item_attributes+]>[+thelink+]</[+itemtag+]>

<!-- template="muie-archive-list-close-markup" -->
</[+listtag+]>
