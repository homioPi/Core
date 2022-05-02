HomioPi_assign('locale', {
    translate: (key, replacements = []) => {
        if(!(key in homiopi.data.locale.translations)) {
            return key;
        }

        let translation = homiopi.data.locale.translations[key]
        let replacements_count = replacements.length;

        for (let i = replacements_count-1; i >= 0; i--) {
            if(i < replacements_count) {
                translation = translation.replace(`%${i}`, replacements[i]);
                continue;
            }
            break;
        }

        return translation;
    }
})
