HomioPi_assign('page', {
    load: (page, reload = false) => {
        page = trim(trim(trim(page, '/'), '#'), '/');
        window.location.hash = `/${page}`;
        
        if(reload) {
            window.location.reload(true);
        }
    },

    get: (url, reload = false) => {
        let currentPage = HomioPi.page.current();
        let old_animation_duration = parseFloat($('main').attr('data-animation-duration') || 0);
        let reduced_motion = HomioPi.users.currentUser.getSetting('reduced_motion') || false;

        url = trim(trim(trim(url, '/'), '#'), '/');

        if(!reduced_motion) {
            HomioPi.page.animateOut();
        }

        // Show loading icon when old page has animated out but new page has not been received yet
        setTimeout(() => {
            if(HomioPi.page.current() == currentPage) {
                if(HomioPi.page.getStatus() != 'error') {
                    HomioPi.page.setStatus('animating_loading');
                }
            }
        }, old_animation_duration);

        // Make a request to load the page
        HomioPi.api.call('page', {'url': url}).then((response) => {
            let new_animation_duration = response.data.manifest.animation_duration || 0;

            $('body').attr('data-page', response.data.manifest.name.trim('/'));
            $('main').attr('data-animation-duration', new_animation_duration);
            
            // Wait for the old page to finish animating out
            setTimeout(() => { 
                if(reload) {
                    window.location.hash = `#/${url}`;
                    window.location.reload(true);
                    return true;
                }

                $('main').html(response.data.html);
               
                // Make the new sidenav item active
                $('.sidenav-link').removeClass('active');
                $(`.sidenav-link[data-target="${response.data.manifest.name.split('/')[0]}"]`).addClass('active');
                
                $(document).trigger('homiopi.load');

                HomioPi.page.setStatus('animating_in');
                HomioPi.page.animateIn(new_animation_duration);
            }, Math.max(old_animation_duration - response.took, 0));
        }).catch((err) => {
            console.error(err);
            $('main').html('');
            HomioPi.page.setStatus('error');
        });
    },

    animateOut: () => {
        HomioPi.page.setStatus('animating_out');
        $('.popup-shield').removeClass('show');
    },

    animateIn: (animation_duration) => {
        HomioPi.page.animationSetup(animation_duration);
        
        HomioPi.page.setStatus('animating_in');
        setTimeout(() => {
            $(document).trigger('homiopi.ready');
            console.log('PAGE READY!');
            HomioPi.page.setStatus('ready');
        }, animation_duration);
    },

    animationSetup: (animation_duration) => {
        let transition_elements = {'fade': [], 'slide': []};
        transition_elements = {
            'fade': {
                'default': $('.transition-fade'),
                'order': $('.transition-fade-order'),
                'random': $('.transition-fade-random')
            },
            'slide': $('.transition-slide-top, .transition-slide-right, .transition-slide-bottom, .transition-slide-left')
        }

        transition_elements['fade']['default'].each(function(index, elem) {
            $(elem).css('transition', `${animation_duration}ms opacity`);
        });

        transition_elements['fade']['order'].each(function(index, elem) {
            let total = Math.max($(elem).siblings('.transition-fade-order').length, 1); // Can't be zero or negative
            let transition_delay = Math.round(index / total * animation_duration);
            $(elem).css('transition', `${animation_duration}ms opacity ${transition_delay}ms`);
        });

        transition_elements['fade']['random'].each(function(index, elem) {
            let transition_delay = Math.floor(Math.random() * (animation_duration/2 + 1));
            $(elem).css('transition', `${animation_duration-transition_delay}ms opacity ${transition_delay}ms`);
        });

        transition_elements['slide'].each(function(index, elem) {
            $(elem).css('transition', `${animation_duration}ms transform, ${animation_duration}ms opacity ease-in-out`);
        });
    },

    current: (include_query = false) => {
        const page  = $('body').attr('data-page');
        const query = window.location.hash.split('?')[1] || '';
        
        return include_query ? page + '?' + query : page;
    },

    getStatus: () => {
        return $('body').attr('data-status');
    },

    setStatus: (status) => {
        $('body').attr('data-status', status);
        return HomioPi.page;
    },

    reload: () => {
        console.log(HomioPi.page.current(true));
        HomioPi.page.get(HomioPi.page.current(true));
        return HomioPi.page;
    }
})

$(window).on('load', function() {
	let page = window.location.hash.substring(1);

    if(page == '') {
	    HomioPi.page.get('home/main');
    } else {
        HomioPi.page.get(page);
    }
})

$(window).on('hashchange', function() {
	let page = window.location.hash.substring(1);
	HomioPi.page.get(page);
})