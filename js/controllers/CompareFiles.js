
app.controller('CompareFiles', function ($scope, CompareFactory, $mdDialog) {

    $scope.spath = 'C:\\xampp\\htdocs\\clickandboat\\text1.txt';
    $scope.fpath = 'C:\\xampp\\htdocs\\clickandboat\\text2.txt';

    $scope.identicalPhrases = [];


    $scope.findDuplicatePhrases = function (ev) {




        $mdDialog.show({

            templateUrl: 'views/dialog.html',
            parent: angular.element(document.body),
            targetEvent: ev,
            clickOutsideToClose: false,
            fullscreen: $scope.customFullscreen, // Only for -xs, -sm breakpoints.
            scope: $scope
        })
                .then(function (answer) {
                    $scope.status = 'You said the information was "' + answer + '".';
                }, function () {
                    $scope.status = 'You cancelled the dialog.';
                });

        CompareFactory.get([$scope.spath, $scope.fpath])
                .then(function (response) {

                    $scope.identicalPhrases = response;
                });


        setTimeout(function () {

            var ws = new WebSocket("ws://localhost:3030");
            ws.onopen = function (event) {
                console.info('open');
                ws.send("hello");
            };

            ws.onclose = function (event) {
                console.info('close');
            };

            ws.onerror = function (event) {
                console.info('error');
            };

            ws.onmessage = function (event) {
                    $scope.identicalPhrases = $scope.identicalPhrases.concat(JSON.parse(event.data));
                    $scope.$apply();
            };

        }, 2000);
    };
});

