
app.controller('CompareFiles', function ($scope, CompareFactory, $mdDialog) {

    $scope.spath = 'c:\\wamp\\www\\clickandboat\\cap\\text1.txt';
    $scope.fpath = 'c:\\wamp\\www\\clickandboat\\cap\\text2.txt';

    $scope.identicalPhrases = '';


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
    };
});

