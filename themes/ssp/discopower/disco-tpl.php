<?php

$faventry = NULL;
foreach( $this->data['idplist'] AS $tab => $slist) {
  if (!empty($this->data['preferredidp']) && array_key_exists($this->data['preferredidp'], $slist))
    $faventry = $slist[$this->data['preferredidp']];
}


if(!array_key_exists('header', $this->data)) {
  $this->data['header'] = 'selectidp';
}
$this->data['header'] = $this->t($this->data['header']);
$this->data['jquery'] = array('core' => TRUE, 'ui' => TRUE, 'css' => TRUE);

// $this->data['head'] = '<link rel="stylesheet" media="screen" type="text/css" href="' . SimpleSAML_Module::getModuleUrl('discopower/style.css')  . '" />';

$this->data['head'] .= '<script type="text/javascript" src="' . SimpleSAML_Module::getModuleUrl('discopower/js/jquery.livesearch.js')  . '"></script>';
$this->data['head'] .= '<script type="text/javascript" src="' . SimpleSAML_Module::getModuleUrl('discopower/js/' . $this->data['score'] . '.js')  . '"></script>';

$this->data['head'] .= '<script type="text/javascript">

  $(document).ready(function() {';

    $i = 0;
    foreach ($this->data['idplist'] AS $tab => $slist) {
      $this->data['head'] .= "\n" . '$("#query_' . $tab . '").liveUpdate("#list_' . $tab . '")' .
        (($i++ == 0) && (empty($faventry)) ? '.focus()' : '') .
';';


    }

    $this->data['head'] .= '
});

</script>';





if (!empty($faventry)) $this->data['autofocus'] = 'favouritesubmit';

$this->includeAtTemplateBase('includes/header.php');

function showEntry($t, $metadata, $favourite = FALSE) {

  $basequerystring = '?' .
    'entityID=' . urlencode($t->data['entityID']) . '&amp;' .
    'return=' . urlencode($t->data['return']) . '&amp;' .
    'returnIDParam=' . urlencode($t->data['returnIDParam']) . '&amp;idpentityid=';

  $extra = ($favourite ? ' favourite' : '');
  $html = '<a class="metaentry' . $extra . '" href="' . $basequerystring . urlencode($metadata['entityid']) . '">';

  $html .= '' . htmlspecialchars(getTranslatedName($t, $metadata)) . '';

  if(array_key_exists('icon', $metadata) && $metadata['icon'] !== NULL) {
    $iconUrl = \SimpleSAML\Utils\HTTP::resolveURL($metadata['icon']);
    $html .= '<img alt="Identity Provider" class="entryicon" src="' . htmlspecialchars($iconUrl) . '" />';
  }

  $html .= '</a>';

  return $html;
}

?>




<?php

function getTranslatedName($t, $metadata) {
  if (isset($metadata['UIInfo']['DisplayName'])) {
    $displayName = $metadata['UIInfo']['DisplayName'];
    assert('is_array($displayName)'); // Should always be an array of language code -> translation
    if (!empty($displayName)) {
      return $t->getTranslation($displayName);
    }
  }

  if (array_key_exists('name', $metadata)) {
    if (is_array($metadata['name'])) {
      return $t->getTranslation($metadata['name']);
    } else {
      return $metadata['name'];
    }
  }
  return $metadata['entityid'];
}




if (!empty($faventry)) {


  echo('<div class="panel panel-default favourite">');
  echo($this->t('previous_auth'));
  echo(' <strong>' . htmlspecialchars(getTranslatedName($this, $faventry)) . '</strong>');
  echo('
  <form id="idpselectform" method="get" action="' . $this->data['urlpattern'] . '" class="b-form-favourite">
    <input type="hidden" name="entityID" value="' . htmlspecialchars($this->data['entityID']) . '" />
    <input type="hidden" name="return" value="' . htmlspecialchars($this->data['return']) . '" />
    <input type="hidden" name="returnIDParam" value="' . htmlspecialchars($this->data['returnIDParam']) . '" />
    <input type="hidden" name="idpentityid" value="' . htmlspecialchars($faventry['entityid']) . '" />

    <input type="submit" name="formsubmit" id="favouritesubmit" class="btn btn-default" value="' . $this->t('login_at') . ' ' . htmlspecialchars(getTranslatedName($this, $faventry)) . '" />
  </form>');

  echo('</div>'); // /panel
}


?>

<?php

foreach( $this->data['idplist'] AS $tab => $slist) {
  if ($tab !== 'all') {
    echo '<div class="panel panel-default b-panel-providers">
            <div class="panel=heading">
              <h3 class="panel-title">' . $this->t('{discopower:tabs:' . $tab . '}') . '</h3>' .
            '</div>';
    if (!empty($slist)) {

      echo('	<div class="inlinesearch">');
      echo('	<p>Incremental search...</p>');
      echo('	<form id="idpselectform" action="?" method="get"><input class="inlinesearchf" type="text" value="" name="query_' . $tab . '" id="query_' . $tab . '" /></form>');
      echo('	</div>');

      echo('	<ul class="metalist b-panel-providers__list" id="list_' . $tab  . '">');
      if (!empty($this->data['preferredidp']) && array_key_exists($this->data['preferredidp'], $slist)) {
        $idpentry = $slist[$this->data['preferredidp']];
        echo '<li style="background:yellow;">';
        echo (showEntry($this, $idpentry, TRUE));
        echo '</li>';
      }

      foreach ($slist AS $idpentry) {
        if ($idpentry['entityid'] != $this->data['preferredidp']) {
        echo '<li style="background:beige;">';
          echo (showEntry($this, $idpentry));
        echo '</li>';
        }
      }
      echo('	</ul>'); // /metalist
    }
  }
  echo '</div>'; // /panel

}

?>





<?php $this->includeAtTemplateBase('includes/footer.php');