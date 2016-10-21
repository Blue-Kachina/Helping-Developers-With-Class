public function load($param_communicationPosition_pk) {
    $pk_boundParamType = $this->GetBoundParamTypeString(array('<<primaryKeyFieldname>>'));
    $db = get_db_connection();
    $sql = 'SELECT * FROM `<<tablename>>` WHERE `<<primaryKeyFieldName>>` = ?';
    $rs = $db->query($sql, null, null, array($pk_boundParamType,$param_communicationPosition_pk));

    if($rs && $rs->rowCount() > 0) {
        $row = $rs->fetch(CoreDB::FETCH_ASSOC);
        $this->loadFromArray($row);
    }
}
