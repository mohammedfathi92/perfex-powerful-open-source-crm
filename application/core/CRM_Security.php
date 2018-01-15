<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CRM_Security extends CI_Security
{
    public function __construct()
    {
        parent::__construct();
    }

    // --------------------------------------------------------------------

    /**
     * XSS Clean
     *
     * Sanitizes data so that Cross Site Scripting Hacks can be
     * prevented.  This method does a fair amount of work but
     * it is extremely thorough, designed to prevent even the
     * most obscure XSS attempts.  Nothing is ever 100% foolproof,
     * of course, but I haven't been able to get anything passed
     * the filter.
     *
     * Note: Should only be used to deal with data upon submission.
     *   It's not something that should be used for general
     *   runtime processing.
     *
     * @link    http://channel.bitflux.ch/wiki/XSS_Prevention
     *      Based in part on some code and ideas from Bitflux.
     *
     * @link    http://ha.ckers.org/xss.html
     *      To help develop this script I used this great list of
     *      vulnerabilities along with a few other hacks I've
     *      harvested from examining vulnerabilities in other programs.
     *
     * @param   string|string[] $str        Input data
     * @param   bool        $is_image   Whether the input is an image
     * @return  string
     */
    public function xss_clean($str, $is_image = false)
    {
        // Is the string an array?
        if (is_array($str)) {
            foreach ($str as $key => &$value) {
                $str[$key] = $this->xss_clean($value);
            }

            return $str;
        }

        // Remove Invisible Characters
        $str = remove_invisible_characters($str);

        /*
         * URL Decode
         *
         * Just in case stuff like this is submitted:
         *
         * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
         *
         * Note: Use rawurldecode() so it does not remove plus signs
         */
        if (stripos($str, '%') !== false) {
            // CUSTOM CODE
            $search = array('/\s%/i', '/%\s/i');
            $replace = array('<p-tmp1></p-tmp1>', '<p-tmp2></p-tmp2>');
            $str = preg_replace($search, $replace, $str);

            do {
                $oldstr = $str;
                $str = rawurldecode($str);
                $str = preg_replace_callback('#%(?:\s*[0-9a-f]){2,}#i', array($this, '_urldecodespaces'), $str);
            } while ($oldstr !== $str);

            unset($oldstr);

             // CUSTOM CODE
            $str = str_replace('<p-tmp1></p-tmp1>', ' %', $str);
            $str = str_replace('<p-tmp2></p-tmp2>', '% ', $str);
        }

        /*
         * Convert character entities to ASCII
         *
         * This permits our tests below to work reliably.
         * We only convert entities that are within tags since
         * these are the ones that will pose security problems.
         */
        $str = preg_replace_callback("/[^a-z0-9>]+[a-z0-9]+=([\'\"]).*?\\1/si", array($this, '_convert_attribute'), $str);
        $str = preg_replace_callback('/<\w+.*/si', array($this, '_decode_entity'), $str);

        // Remove Invisible Characters Again!
        $str = remove_invisible_characters($str);

        /*
         * Convert all tabs to spaces
         *
         * This prevents strings like this: ja  vascript
         * NOTE: we deal with spaces between characters later.
         * NOTE: preg_replace was found to be amazingly slow here on
         * large blocks of data, so we use str_replace.
         */
        $str = str_replace("\t", ' ', $str);

        // Capture converted string for later comparison
        $converted_string = $str;

        // Remove Strings that are never allowed
        $str = $this->_do_never_allowed($str);

        /*
         * Makes PHP tags safe
         *
         * Note: XML tags are inadvertently replaced too:
         *
         * <?xml
         *
         * But it doesn't seem to pose a problem.
         */
        if ($is_image === true) {
            // Images have a tendency to have the PHP short opening and
            // closing tags every so often so we skip those and only
            // do the long opening tags.
            $str = preg_replace('/<\?(php)/i', '&lt;?\\1', $str);
        } else {
            $str = str_replace(array('<?', '?'.'>'), array('&lt;?', '?&gt;'), $str);
        }

        /*
         * Compact any exploded words
         *
         * This corrects words like:  j a v a s c r i p t
         * These words are compacted back to their correct state.
         */
        $words = array(
            'javascript', 'expression', 'vbscript', 'jscript', 'wscript',
            'vbs', 'script', 'base64', 'applet', 'alert', 'document',
            'write', 'cookie', 'window', 'confirm', 'prompt', 'eval',
        );

        foreach ($words as $word) {
            $word = implode('\s*', str_split($word)).'\s*';

            // We only want to do this when it is followed by a non-word character
            // That way valid stuff like "dealer to" does not become "dealerto"
            $str = preg_replace_callback('#('.substr($word, 0, -3).')(\W)#is', array($this, '_compact_exploded_words'), $str);
        }

        /*
         * Remove disallowed Javascript in links or img tags
         * We used to do some version comparisons and use of stripos(),
         * but it is dog slow compared to these simplified non-capturing
         * preg_match(), especially if the pattern exists in the string
         *
         * Note: It was reported that not only space characters, but all in
         * the following pattern can be parsed as separators between a tag name
         * and its attributes: [\d\s"\'`;,\/\=\(\x00\x0B\x09\x0C]
         * ... however, remove_invisible_characters() above already strips the
         * hex-encoded ones, so we'll skip them below.
         */
        do {
            $original = $str;

            if (preg_match('/<a/i', $str)) {
                $str = preg_replace_callback('#<a(?:rea)?[^a-z0-9>]+([^>]*?)(?:>|$)#si', array($this, '_js_link_removal'), $str);
            }

            if (preg_match('/<img/i', $str)) {
                $str = preg_replace_callback('#<img[^a-z0-9]+([^>]*?)(?:\s?/?>|$)#si', array($this, '_js_img_removal'), $str);
            }

            if (preg_match('/script|xss/i', $str)) {
                $str = preg_replace('#</*(?:script|xss).*?>#si', '[removed]', $str);
            }
        } while ($original !== $str);
        unset($original);

        /*
         * Sanitize naughty HTML elements
         *
         * If a tag containing any of the words in the list
         * below is found, the tag gets converted to entities.
         *
         * So this: <blink>
         * Becomes: &lt;blink&gt;
         */
        $pattern = '#'
            .'<((?<slash>/*\s*)((?<tagName>[a-z0-9]+)(?=[^a-z0-9]|$)|.+)' // tag start and name, followed by a non-tag character
            .'[^\s\042\047a-z0-9>/=]*' // a valid attribute character immediately after the tag would count as a separator
            // optional attributes
            .'(?<attributes>(?:[\s\042\047/=]*' // non-attribute characters, excluding > (tag close) for obvious reasons
            .'[^\s\042\047>/=]+' // attribute characters
            // optional attribute-value
                .'(?:\s*=' // attribute-value separator
                    .'(?:[^\s\042\047=><`]+|\s*\042[^\042]*\042|\s*\047[^\047]*\047|\s*(?U:[^\s\042\047=><`]*))' // single, double or non-quoted value
                .')?' // end optional attribute-value group
            .')*)' // end optional attributes group
            .'[^>]*)(?<closeTag>\>)?#isS';

        // Note: It would be nice to optimize this for speed, BUT
        //       only matching the naughty elements here results in
        //       false positives and in turn - vulnerabilities!
        do {
            $old_str = $str;
            $str = preg_replace_callback($pattern, array($this, '_sanitize_naughty_html'), $str);
        } while ($old_str !== $str);
        unset($old_str);

        /*
         * Sanitize naughty scripting elements
         *
         * Similar to above, only instead of looking for
         * tags it looks for PHP and JavaScript commands
         * that are disallowed. Rather than removing the
         * code, it simply converts the parenthesis to entities
         * rendering the code un-executable.
         *
         * For example: eval('some code')
         * Becomes: eval&#40;'some code'&#41;
         */
        $str = preg_replace(
            '#(alert|prompt|confirm|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si',
            '\\1\\2&#40;\\3&#41;',
            $str
        );

        // Final clean up
        // This adds a bit of extra precaution in case
        // something got through the above filters
        $str = $this->_do_never_allowed($str);

        /*
         * Images are Handled in a Special Way
         * - Essentially, we want to know that after all of the character
         * conversion is done whether any unwanted, likely XSS, code was found.
         * If not, we return TRUE, as the image is clean.
         * However, if the string post-conversion does not matched the
         * string post-removal of XSS, then it fails, as there was unwanted XSS
         * code found and removed/changed during processing.
         */
        if ($is_image === true) {
            return ($str === $converted_string);
        }

        return $str;
    }
}
