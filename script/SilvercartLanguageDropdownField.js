
(function($) {
    
    $('.silvercart-change-language-selector li.selectable').live('click', function() {
        $('.silvercart-change-language-selector li.first').removeClass('first').addClass('selectable');
        $(this).removeClass('selectable').addClass('first');
        $('.silvercart-change-language-selector li.selectable').hide();

        var classes = $(this).attr('class').split(' ');

        if (typeof classes != 'object') {
            classes= [classes];
        }

        $('.silvercart-change-language-form select option[value="' + classes[0] + '"]').attr('selected',true);

        $('.silvercart-change-language-form').submit();

    });
    
    $(document).ready(function() {
        var languageSelector        = $('.silvercart-change-language-form select');
        var languageSelectorOptions = $('option', languageSelector);
        var firstLanguage           = true;
        var languageCssClass        = 'first';
        var markup = '<ul class="silvercart-change-language-selector">';
        
        languageSelectorOptions.each(function() {
            var locale  = $(this).val();
            var iso2    = $(this).attr('class');
            var link    = $(this).attr('rel');
            if (firstLanguage) {
                languageCssClass    = 'first';
                firstLanguage       = false;
            } else {
                languageCssClass    = 'selectable';
            }
            var img = '<img src="/silvercart/images/icons/flags/' + iso2 + '.png" alt="" />';
            markup += '<li class="' + locale + ' ' + languageCssClass + '"><a href="' + link + '">' + img + $(this).html() + '</a></li>';
        });
        
        markup += '</ul>';
        
        $('.silvercart-change-language-form').hide();
        $('.silvercart-change-language').append(markup);
    });
})(jQuery);