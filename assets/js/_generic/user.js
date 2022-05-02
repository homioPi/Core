HomioPi_assign('users.currentUser', {
    getSetting: (key) => {
        return HomioPi.data?.users?.currentUser?.settings[key] ?? undefined;
    },

    setSetting: (key, value) => {
        return new Promise((resolve, reject) => {
            HomioPi.api.call('user-set-setting', {'key': key, 'value': value}).then((response) => {
                resolve(response);
            }).catch(() => {
                reject(response);
            })
        })
    }
})