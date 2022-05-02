homioPiAssign('users.currentUser', {
    getSetting: (key) => {
        return homioPi.data?.users?.currentUser?.settings[key] ?? undefined;
    },

    setSetting: (key, value) => {
        return new Promise((resolve, reject) => {
            homioPi.api.call('user-set-setting', {'key': key, 'value': value}).then((response) => {
                resolve(response);
            }).catch(() => {
                reject(response);
            })
        })
    }
})