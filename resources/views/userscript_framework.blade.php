@verbatim


const UrlMarker = (function(url) {
    var counter = 0;
    var urlmarker = {};
    var bootstraped = false;

    function fill_id(action) {
        action._id = `remote-id-${counter++}`;
    }

    function fill_depends(action, dependencies) {
        var i = 0;

        if (dependencies && dependencies.length > 0) {
            action._depends = [];
            for (i = 0; i < dependencies.length; i++) {
                action._depends.push(dependencies[i]._id);
            }
        }
    }

    urlmarker.bootstrap = function(callback) {
        if (document.readyState === 'complete' && bootstraped === false) {
            callback();
            bootstraped = true;
        } else {
            document.addEventListener('readystatechange', function() {
                if (document.readyState === 'complete' && bootstraped === false) {
                    callback();
                    bootstraped = true;
                }
            });
        }
    };

    urlmarker.remote = {
        check: function(urls, dependencies) {
            var output = { _type: 'check', urls };
            fill_id(output);
            fill_depends(output, dependencies);
            return output;
        },

        list: function(section, filter, offset, limit, dependencies) {
            var output = { _type: 'list', section, filter, offset, limit };
            fill_id(output);
            fill_depends(output, dependencies);
            return output;
        },

        create: function(url, description, handler, dependencies) {
            var output = { _type: 'create', url, description, handler: handler || 'Website' };
            fill_id(output);
            fill_depends(output, dependencies);
            return output;
        },

        remove_by_id: function(id, dependencies) {
            var output = { _type: 'remove-by-id', id };
            fill_id(output);
            fill_depends(output, dependencies);
            return output;
        },

        remove_by_url: function(url, dependencies) {
            var output = { _type: 'remove-by-url', url };
            fill_id(output);
            fill_depends(output, dependencies);
            return output;
        },

        restore_by_id: function(id, dependencies) {
            var output = { _type: 'restore-by-id', id };
            fill_id(output);
            fill_depends(output, dependencies);
            return output;
        },

        restore_by_url: function(url, dependencies) {
            var output = { _type: 'restore-by-url', url };
            fill_id(output);
            fill_depends(output, dependencies);
            return output;
        },

        call: function(actions) {
            return new Promise(function(resolve, reject) {
                console.log('Calling remote actions: ', actions);

                GM.xmlHttpRequest({
                    url: url,
                    method: 'POST',
                    data: JSON.stringify({ actions }),
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },

                    onload: function(response) {
                        if (response.status == 200) {
                            try {
                                var data = JSON.parse(response.responseText);

                                if (data.accepted) {
                                    console.log('Completed calling remote actions: ', data.results);
                                    resolve(data.results);
                                } else {
                                    console.log('Failed calling remote actions due to invalid request error: ', response);
                                    reject(new Error('Invalid Request Error: ' + data.error));
                                }
                            } catch (err) {
                                console.log('Failed calling remote actions due to invalid response error: ', response);
                                reject(new Error('Invalid Response Error'));
                            }
                        } else if (response.status == 400) {
                            console.log('Failed calling remote actions due to invalid request error: ', response);
                            reject(new Error('Invalid Request Error'));
                        } else if (response.status == 401 || response.status == 403) {
                            console.log('Failed calling remote actions due to authentication error: ', response);
                            reject(new Error('Authentication Error'));
                        } else if (response.status == 404) {
                            console.log('Failed calling remote actions due to client config error: ', response);
                            reject(new Error('Client Config Error'));
                        } else if (response.status >= 500) {
                            console.log('Failed calling remote actions due to general server error: ', response);
                            reject(new Error('General Server error'));
                        } else if (response.status >= 404) {
                            console.log('Failed calling remote actions due to general client error: ', response);
                            reject(new Error('General Client error'));
                        } else {
                            console.log('Failed calling remote actions due to unknown error: ', response);
                            reject(new Error('Unkonwn error'));
                        }
                    },

                    onerror: function(response) {
                        console.log('Failed calling remote actions due to network error: ', response);
                        reject(new Error('Network error'));
                    },
                });
            });
        },
    };

    urlmarker.utils = {
        datauri: function(data, type, urlencode) {
            if (btoa && !urlencode) {
                return "data:" + type + ";base64," + btoa(data);
            } else {
                return "data:" + type + ";" + encodeURIComponent(data);
            }
        },
    };

    Object.freeze(urlmarker.remote);
    Object.freeze(urlmarker);
    return urlmarker;
})(
@endverbatim
    {!! json_encode(route('action')) !!}
@verbatim
);


@endverbatim
