<?php

final class PhabricatorClusterSearchConfigOptionType
  extends PhabricatorConfigJSONOptionType {

  public function validateOption(PhabricatorConfigOption $option, $value) {
    if (!is_array($value)) {
      throw new Exception(
        pht(
          'Search cluster configuration is not valid: value must be a '.
          'list of search hosts.'));
    }

    $types = PhabricatorSearchCluster::getValidHostTypes();

    foreach ($value as $index => $spec) {
      if (!is_array($spec)) {
        throw new Exception(
          pht(
            'Search cluster configuration is not valid: each entry in the '.
            'list must be a dictionary describing a search service, but '.
            'the value with index "%s" is not a dictionary.',
            $index));
      }

      try {
        PhutilTypeSpec::checkMap(
          $spec,
          array(
            'type'      => 'string',
            'hosts'     => 'optional list<map<string, wild>>',
            'roles'     => 'optional map<string, wild>',
            'port'      => 'optional int',
            'protocol'  => 'optional string',
            'path'      => 'optional string',
            'version'   => 'optional int',
          ));
      } catch (Exception $ex) {
        throw new Exception(
          pht(
            'Search cluster configuration has an invalid service '.
            'specification (at index "%s"): %s.',
            $index,
            $ex->getMessage()));
      }

      if (!array_key_exists($spec['type'], $types)) {
        throw new Exception(
          pht('Invalid search cluster type: %s. Valid types include: %s',
            $spec['type'],
            implode(', ', array_keys($types))));
      }

      if (isset($spec['hosts'])) {
        foreach ($spec['hosts'] as $hostindex => $host) {
          try {
            PhutilTypeSpec::checkMap(
              $host,
              array(
                'host'     => 'string',
                'roles'     => 'optional map<string, wild>',
                'port'      => 'optional int',
                'protocol'  => 'optional string',
                'path'      => 'optional string',
                'version'   => 'optional int',
              ));
          } catch (Exception $ex) {
            throw new Exception(
              pht(
                'Search cluster configuration has an invalid host '.
                'specification (at index "%s"): %s.',
                $hostindex,
                $ex->getMessage()));
          }
        }
      }
    }
  }
}