
app.factory('CompareFactory', function ($http) {

    return {
        get: function (paths) {
            return $http.post('index.php', paths)
                    .then(
                            function (data) {
                                return data.data;
                            },
                            function (error) {
                                console.log(error);
                            }
                    );
        }
    };

});

