<?php create_script('Bootstrap')
    ->includeDependency('BootstrapIcons')
    ->includeScript() ?>
<?php use_stylesheet('css/demo.css') ?>
<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
  <div class="container">
    <a class="navbar-brand" href="https://ntlab.id/demo/dpfb-demo">DPFB Demo</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsDemo" aria-controls="navbarsDemo" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarsDemo">
      <ul class="navbar-nav me-auto"></ul>
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="https://github.com/tohenk/php-dpfb-demo">Source Code</a></li>
        <li class="nav-item"><a class="nav-link" href="https://github.com/tohenk/node-dpfb"><span class="bi-github"></span></a></li>
      </ul>
    </div>
  </div>
</nav>
<main role="main">
  <!-- Main jumbotron for a primary marketing message or call to action -->
  <div class="jumbotron">
    <div class="container">
      <h1 class="display-3 my-5">Digital Persona Fingerprint Bridge Demo</h1>
      <p class="lead">This demo shows you how to integrate <code>NODE-DPFB</code> into your PHP application. You must have already a Digital Persona fingerprint reader configured to allow this demo to work properly.</p>
      <p class="lead">The DPFP app can be downloaded from <a href="https://github.com/tohenk/node-dpfb/releases">Github</a>. You also need to run the server portion of the App to be able to enroll and identify finger. Get the server from <a href="https://github.com/tohenk/node-dpfb">here</a>, then issue: <code>npm run fpserver</code>.</p>
      <div class="alert alert-warning" role="alert">
        <span class="text-danger">Important!</span> When capturing the fingerprint, make sure the DPFB app has focus, not this browser.
      </div>
    </div>
  </div>
  <div class="container">
    <!-- Example row of columns -->
    <div class="row">
      <div class="col-md-4 status">
        <h2>Status</h2>
        <p class="connected d-none"><span class="text-success">Cool!</span> <code>Digital Persona Fingerprint Bridge</code> is already running.</p>
        <p class="disconnected"><code>Digital Persona Fingerprint Bridge</code> is not running. Please run DPFB app on your local computer to start!</p>
        <p>
          <a class="btn btn-danger btn-lg d-none btn-clear" href="#" role="button"><span class="bi-hand-index"></span> Clear</a>
        </p>
      </div>
      <div class="col-md-4 enroll">
        <h2>Enrollment</h2>
        <p>To start enrollment, use the button below. You need to run the DPFB app if it is not visible!</p>
        <p>
          <a class="btn btn-success btn-lg d-none btn-enroll" href="#" role="button"><span class="bi-hand-index"></span> Enroll</a>
          <a class="btn btn-success btn-lg d-none btn-unenroll" href="#" role="button"><span class="bi-hand-index"></span> Unenroll</a>
        </p>
      </div>
      <div class="col-md-4 indentification">
        <h2>Identification</h2>
        <p>To start identification, use the button below. You need to run the DPFB app if it is not visible and you have already enrolled!</p>
        <p><a class="btn btn-primary btn-lg d-none btn-identify" href="#" role="button"><span class="bi-hand-index"></span> Identify</a></p>
      </div>
    </div>
    <hr>
  </div> <!-- /container -->
</main>
<footer class="container"><p>&copy; 2021 NTLAB.ID</p></footer>
<?php $clear              = __('Clear Registered Templates') ?>
<?php $clear_message      = __('This will clear all registered templates within server. Do you want to continue?') ?>
<?php $clear_success      = __('Registered templates cleared successfully.') ?>
<?php $clear_unsuccess    = __('Unable to clear registered templates, server may be not running!') ?>
<?php $enroll             = __('Finger Enrollment') ?>
<?php $enroll_next        = __('Finger Enrollment success, do you want to continue with next finger?') ?>
<?php $enroll_success     = __('Finger Enrollment done successfully.') ?>
<?php $enroll_unsuccess   = __('Finger Enrollment not saved, please try again!') ?>
<?php $unenroll           = __('Finger Unenrollment') ?>
<?php $unenroll_success   = __('Finger Unenrollment done successfully.') ?>
<?php $unenroll_unsuccess = __('Finger Unenrollment unsuccessfull, please try again!') ?>
<?php $identify           = __('Finger Identification') ?>
<?php $identity_match     = __('Finger Identification matched your <code>%FINGER%</code>.') ?>
<?php $identity_nomatch   = __('Finger Identification matched with id which is not in your fingers. Clear registered templates may solve the problem!') ?>
<?php $fp_clear_url       = 'a/fp-clear' ?>
<?php $fp_save_url        = 'a/fp-save?idx=_INDEX_' ?>
<?php $fp_del_url         = 'a/fp-del' ?>
<?php $fp_identify_url    = 'a/fp-verify' ?>
<?php create_script('Fingerprint')
    ->includeDependency(['Bootstrap.Dialog.Confirm', 'Bootstrap.Dialog.Message', 'JsCookie'])
    ->includeScript()
    ->add(<<<EOF
var cookie = 'fingerprints';
var fingers = {};

// update ui when fingerprint connected or disconnected
$(document).on('fpconnect', function(e) {
    $.fpUpdate();
});

// button handler
$('a.btn-clear').on('click', function(e) {
    e.preventDefault();
    $.fpClear();
});
$('a.btn-enroll').on('click', function(e) {
    e.preventDefault();
    $.fpOp(1);
});
$('a.btn-unenroll').on('click', function(e) {
    e.preventDefault();
    $.fpOp(2);
});
$('a.btn-identify').on('click', function(e) {
    e.preventDefault();
    $.fp.acquire();
});

// update UI according to states
$.fpUpdate = function() {
    if ($.fp.connected) {
        $('.status .connected').removeClass('d-none');
        $('.status .disconnected').addClass('d-none');
        $('a.btn-clear').removeClass('d-none');
        $('a.btn-enroll').removeClass('d-none');
        if (Object.keys(fingers).length > 0) {
            $('a.btn-unenroll').removeClass('d-none');
            $('a.btn-identify').removeClass('d-none');
        } else {
            $('a.btn-unenroll').addClass('d-none');
            $('a.btn-identify').addClass('d-none');
        }
    } else {
        $('.status .connected').addClass('d-none');
        $('.status .disconnected').removeClass('d-none');
        $('a.btn-clear').addClass('d-none');
        $('a.btn-enroll').addClass('d-none');
        $('a.btn-unenroll').addClass('d-none');
        $('a.btn-identify').addClass('d-none');
    }
}

$.fpGetFromCookie = function() {
    var fps = Cookies.get(cookie);
    if (fps) {
        fingers = JSON.parse(fps);
    }
}

$.fpSaveToCookie = function(update) {
    Cookies.set(cookie, fingers);
    if (update) $.fpUpdate();
}

// clear registered templates
$.fpClear = function() {
    $.ntdlg.confirm('my-fp-msg', '$clear', '$clear_message', $.ntdlg.ICON_QUESTION, function() {
        $.post('$fp_clear_url')
            .done(function(json) {
                if (json.success) {
                    fingers = {};
                    $.fpSaveToCookie(true);
                    $.ntdlg.message('my-fp-msg', '$clear', '$clear_success', $.ntdlg.ICON_SUCCESS);
                } else {
                    $.ntdlg.message('my-fp-msg', '$clear', '$clear_unsuccess', $.ntdlg.ICON_ERROR);
                }
             })
        ; 
    });
}

// fingerprint operation, 1 for enroll, 2 for unenroll
$.fpOp = function(op) {
    var mask = 0;
    Object.keys($.fp.fingers).forEach(function(finger) {
        var idx = $.fp.fingers[finger].index;
        if (fingers[idx]) {
            mask = $.fp.includeFinger(mask, idx);
        }
    });
    switch (op) {
    case 1:
        $.fp.enroll(mask);
        break;
    case 2:
        $.fp.unenroll(mask);
        break;
    }
}

// initialize fingerprint
$.fp.init(function(status, data) {
    switch (status) {
    case 'finger-acquired':
        $.fp.startVerifyTimer();
        $.ajax({
            url: '$fp_identify_url',
            type: 'POST',
            data: data,
            contentType: 'application/octet-stream',
            processData: false
        }).done(function(json) {
            $.fp.stopVerifyTimer();
            if (json.success) {
                if (json.id) {
                    $.fp.closeVerifyDialog();
                    var idx;
                    Object.keys(fingers).forEach(function(finger) {
                        if (fingers[finger].id == json.id) {
                            idx = finger;
                        }
                    });
                    if (idx) {
                        $.ntdlg.message('my-fp-msg', '$identify', '$identity_match'.replace(/%FINGER%/, $.fp.getFingerInfo($.fp.getFingerId(idx), 'fullname')), $.ntdlg.ICON_SUCCESS);
                    } else {
                        $.ntdlg.message('my-fp-msg', '$identify', '$identity_nomatch', $.ntdlg.ICON_ERROR);
                    }
                } else {
                    // retry
                    $.fp.retryAcquire();
                }
            }
        });
        break;
    case 'finger-enrolled':
        $.ajax({
            url: '$fp_save_url'.replace(/_INDEX_/, $.fp.fingerIndex),
            type: 'POST',
            data: data,
            contentType: 'application/octet-stream',
            processData: false
        }).done(function(json) {
            if (json.success) {
                fingers[$.fp.fingerIndex] = {id: json.id, data: data};
                $.fpSaveToCookie(true);
                var mask = $.fp.includeFinger($.fp.fingerMask, $.fp.fingerIndex);
                var nextFinger = $.fp.getNextUnenrolledFinger(mask);
                if (nextFinger > 0) {
                    $.ntdlg.confirm('my-fp-msg', '$enroll', '$enroll_next', $.ntdlg.ICON_QUESTION, function() {
                        $.fp.enroll(mask, nextFinger); 
                    });
                } else {
                    $.ntdlg.message('my-fp-msg', '$enroll', '$enroll_success', $.ntdlg.ICON_SUCCESS);
                }
            } else {
                $.ntdlg.message('my-fp-msg', '$enroll', '$enroll_unsuccess', $.ntdlg.ICON_ERROR);
            }
        });
        break;
    case 'finger-unenrolled':
        var ids = [];
        data.forEach(function(finger) {
            if (fingers[finger]) {
                ids.push(fingers[finger].id);
            }
        });
        if (ids.length) {
            $.post('$fp_del_url', {id: ids})
                .done(function(json) {
                    if (json.success) {
                        if (json.deleted) {
                            console.log('Deleted: %s', JSON.stringify(json.deleted));
                            Object.keys(fingers).forEach(function(finger) {
                                if (json.deleted.indexOf(fingers[finger].id) >= 0) {
                                    delete fingers[finger];
                                }
                            });
                            $.fpSaveToCookie(true);
                        } else {
                            console.log('No fingers deleted, assume as not registered!');
                            data.forEach(function(finger) {
                                if (fingers[finger]) {
                                    delete fingers[finger];
                                }
                            });
                            $.fpSaveToCookie(true);
                        }
                        $.ntdlg.message('my-fp-msg', '$unenroll', '$unenroll_success', $.ntdlg.ICON_SUCCESS);
                    } else {
                        $.ntdlg.message('my-fp-msg', '$unenroll', '$unenroll_unsuccess', $.ntdlg.ICON_ERROR);
                    }
                 })
            ;
        } 
        break;
    }
});
$.fpGetFromCookie();
EOF
    ) ?>