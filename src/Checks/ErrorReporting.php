<?php

/**
 * @file
 * Contains \Drupal\security_review\Checks\BaseUrl.
 */

namespace Drupal\security_review\Checks;

use Drupal\Core\Url;
use Drupal\security_review\Check;
use Drupal\security_review\CheckResult;

/**
 * Defines a security check that checks the error reporting setting.
 */
class ErrorReporting extends Check {

  /**
   * {@inheritdoc}
   */
  public function getNamespace() {
    return 'Security Review';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return 'Error reporting';
  }

  /**
   * {@inheritdoc}
   */
  public function run() {
    // Get the error level.
    $error_level = $this->configFactory()->get('system.logging')
      ->get('error_level');

    // Determine the result.
    if (is_null($error_level) || $error_level != 'hide') {
      $result = CheckResult::FAIL;
    }
    else {
      $result = CheckResult::SUCCESS;
    }

    return $this->createResult($result, array('level' => $error_level));
  }

  /**
   * {@inheritdoc}
   */
  public function help() {
    $paragraphs = array();
    $paragraphs[] = $this->t('As a form of hardening your site you should avoid information disclosure. Drupal by default prints errors to the screen and writes them to the log. Error messages disclose the full path to the file where the error occurred.');

    return array(
      '#theme' => 'check_help',
      '#title' => $this->t('Error reporting'),
      '#paragraphs' => $paragraphs,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(CheckResult $result) {
    if ($result->result() == CheckResult::SUCCESS) {
      return array();
    }

    $paragraphs = array();
    $paragraphs[] = $this->t('You have error reporting set to both the screen and the log.');
    $paragraphs[] = $this->l(
      $this->t('Alter error reporting settings.'),
      Url::fromRoute('system.logging_settings')
    );

    return array(
      '#theme' => 'check_evaluation',
      '#paragraphs' => $paragraphs,
      '#items' => array(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function evaluatePlain(CheckResult $result) {
    if ($result->result() == CheckResult::SUCCESS) {
      return '';
    }

    if (isset($result->findings()['level'])) {
      return $this->t('Error level: !level', array(
        '!level' => $result->findings()['level'],
      ));
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getMessage($result_const) {
    switch ($result_const) {
      case CheckResult::SUCCESS:
        return $this->t('Error reporting set to log only.');

      case CheckResult::FAIL:
        return $this->t('Errors are written to the screen.');

      default:
        return $this->t('Unexpected result.');
    }
  }

}
