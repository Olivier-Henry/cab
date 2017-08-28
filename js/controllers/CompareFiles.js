
app.controller('CompareFiles', function ($scope, CompareFactory, $mdDialog) {

    $scope.spath = '/Library/Server/Web/Data/Sites/Default/cab/text1.txt';
    $scope.fpath = '/Library/Server/Web/Data/Sites/Default/cab/text2.txt';

    $scope.identicalPhrases = [];
    $scope.socketInitialised = false;


    $scope.findDuplicatePhrases = function (ev) {

        $scope.identicalPhrases = [];

        $mdDialog.show({

            templateUrl: 'views/dialog.html',
            parent: angular.element(document.body),
            targetEvent: ev,
            clickOutsideToClose: false,
            fullscreen: $scope.customFullscreen, // Only for -xs, -sm breakpoints.
            scope: $scope,
            preserveScope: true
        })
                .then(function () {
                    console.log('dialog closed');
                });
        if (!$scope.ws) {
            CompareFactory.get([$scope.spath, $scope.fpath])
                    .then(function (response) {
                        if(response.length){
                            $scope.identicalPhrases = response;
                        }
                    });
        } else {
            $scope.ws.send(JSON.stringify([$scope.spath, $scope.fpath]));
        }

        if (!$scope.socketInitialised) {
            setTimeout(function () {

                $scope.socketInitialised = true;
                $scope.ws = new WebSocket("ws://localhost:3030");
                $scope.ws.onopen = function (event) {
                    $scope.ws.send(JSON.stringify([$scope.fpath, $scope.spath]));
                };

                $scope.ws.onclose = function (event) {
                    console.info('close');
                };

                $scope.ws.onerror = function (event) {
                    console.info('error');
                };

                $scope.ws.onmessage = function (event) {

                    $scope.identicalPhrases = $scope.identicalPhrases.concat(JSON.parse(event.data));

                    $scope.$apply();
                };

            }, 2000);
        } else {
            $scope.ws.send("find duplicates");
        }
    };

    $scope.close = function () {
        $mdDialog.hide();
    };
});

