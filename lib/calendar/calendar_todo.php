<?php

class pz_calendar_todo extends pz_calendar_item
{
  const TABLE = 'pz_calendar_todo';

  protected $priority;

  protected $order;

  /**
   * @var DateTime
   */
  protected $start;

  /**
   * @var DateTime
   */
  protected $due;

  /**
   * @var DateTime
   */
  protected $completed;

  protected function __construct(array $params = array())
  {
    if(isset($params['t.id']))
      $this->id = $params['t.id'];
    if(isset($params['t.uri']))
      $this->uri = $params['t.uri'];
    if(isset($params['t.project_id']))
      $this->project_id = $params['t.project_id'];
    if(isset($params['t.project_sub_id']))
      $this->project_sub_id = $params['t.project_sub_id'];
    if(isset($params['t.clip_ids']))
      $this->clip_ids = $params['t.clip_ids'];
    if(isset($params['t.title']))
      $this->title = $params['t.title'];
    if(isset($params['t.description']))
      $this->description = $params['t.description'];
    if(isset($params['t.priority']))
      $this->priority = $params['t.priority'];
    if(isset($params['t.order']))
      $this->order = $params['t.order'];
    if(isset($params['t.from']))
      $this->from = new DateTime($params['t.from']);
    if(isset($params['t.due']))
      $this->due = new DateTime($params['t.due']);
    if(isset($params['t.completed']))
      $this->completed = new DateTime($params['t.completed']);
    if(isset($params['t.created']))
      $this->created = new DateTime($params['t.created']);
    if(isset($params['t.updated']))
      $this->updated = new DateTime($params['t.updated']);
    if(isset($params['t.sequence']))
      $this->sequence = $params['t.sequence'];
    if(isset($params['t.user_id']))
      $this->user_id = $params['t.user_id'];
    if(isset($params['t.rule_id']))
      $this->rule_id = $params['t.rule_id'];
  }

  public function getPriority()
  {
    return $this->priority;
  }

  public function setPriority($priority)
  {
    $this->setValue('priority', $priority);
  }

  public function getOrder()
  {
    return $this->order;
  }

  public function setOrder($order)
  {
    $this->setValue('order', $order);
  }

  /**
   * @return DateTime
   */
  public function getDue()
  {
    if ($this->due)
      return clone $this->due;
    return null;
  }

  public function setDue(DateTime $due = null)
  {
    $this->setValue('due', $due);
  }

  /**
   * @return DateTime
   */
  public function getCompleted()
  {
    if ($this->completed)
      return clone $this->completed;
    return null;
  }

  public function setCompleted(DateTime $completed = null)
  {
    $this->setValue('completed', $completed);
  }

  public function save()
  {
    $sql = pz_sql::factory()
      ->setTable(self::TABLE);
    $ignore = array('alarms');
    foreach(array_keys($this->changed) as $key)
    {
      if(!in_array($key, $ignore))
        $sql->setValue($key, self::sqlValue($this->$key));
    }
    $sql->setValue('vt', $this->getTitle() .' '. $this->getDescription());
    if(!$this->hasChanged('updated'))
    {
      $sql->setRawValue('updated', 'NOW()');
    }
    if($this->new)
    {
      if(!$this->hasChanged('user_id'))
      {
        $sql->setValue('user_id', pz::getUser()->getId());
      }
      if(!$this->hasChanged('created'))
      {
        $sql->setRawValue('created', 'NOW()');
      }
      if(!$this->hasChanged('uri'))
      {
        $sql->setRawValue('uri', 'CONCAT(UPPER(UUID()), ".ics")');
      }
      $sql->insert();
      $this->id = $sql->getLastId();
    }
    else
    {
      if(!$this->hasChanged('sequence'))
      {
        $sql->setRawValue('sequence', 'sequence + 1');
      }
      $sql->setWhere(array('id' => $this->id))
        ->update();
    }
    if($this->hasChanged('alarms'))
    {
      pz_calendar_alarm::saveAll($this);
    }

    pz_sabre_caldav_backend::incrementCtag($this->project_id);

    $this->changed = array();
    $this->new = false;
  }

  public function delete()
  {
    pz_sql::factory()->setQuery('
      DELETE t, al, r
      FROM '. self::TABLE .' t
      LEFT JOIN '. pz_calendar_alarm::TABLE .' al
      ON al.todo_id = t.id
      LEFT JOIN '. pz_calendar_rule::TABLE .' r
      ON r.todo_id = t.id
      WHERE t.id = ?
    ', array($this->id));

    pz_sabre_caldav_backend::incrementCtag($this->project_id);
  }

  static public function create()
  {
    $todo = new self;
    $todo->new = true;
    return $todo;
  }

  static public function get($id)
  {
    static $sql = null;
    if(!$sql)
    {
      $sql = pz_sql::factory();
      $sql->prepareQuery('
      	SELECT *
      	FROM '. self::TABLE .' t
      	WHERE id = ?
      ');
    }
    $sql->execute(array($id));
    if($sql->getRows() == 0)
    {
      return null;
    }
    return new self($sql->getRow());
  }

  static public function getAll(array $projects)
  {
    if(empty($projects))
      return array();

    $params = $projects;
    $wInClause = implode(',', array_pad(array(), count($projects), '?'));

    $sql = pz_sql::factory();
    $sql->setQuery('
      SELECT *
      FROM '. self::TABLE .' t
      WHERE project_id IN ('. $wInClause .') AND uri != ""
    ', $params);

    $todos = array();
    foreach($sql as $row)
    {
      $todos[] = new self($row->getRow());
    }
    return $todos;
  }

  static public function getByProjectUri($project, $uri)
  {
    static $sql = null;
    if(!$sql)
    {
      $sql = pz_sql::factory();
      $sql->prepareQuery('
      	SELECT *
      	FROM '. self::TABLE .' t
      	WHERE project_id = ? AND uri = ?
      ');
    }
    $sql->execute(array($project, $uri));
    if($sql->getRows() == 0)
    {
      return null;
    }
    return new self($sql->getRow());
  }

  static public function getAllBase($project)
  {
    $sql = pz_sql::factory();
    $sql->setQuery('
      SELECT *
      FROM '. self::TABLE .' t
      WHERE project_id = ? AND uri != ""
    ', array($project));

    $todos = array();
    foreach($sql as $row)
    {
      $todos[] = new self($row->getRow());
    }
    return $todos;
  }
}
