###### Модель данных ######
В составе патерна [[MVC](MVC.md)] модель служит для представления данных приложения. В фреймворке [[IcEngine](IcEngine.md)] модель выполняет точно такую же функцию.
Все модели приложения наследуются от абстрактной модели Model, реализующей патер [[Record](Active.md)]. Что это значит? Для упрощение понимания будем считать, что наша модель данных реализует базы данных Mysql. Тогда эземпляр некой модели Metro будет являться каким-то кортежем таблицы ice\_metro (где ice - префикс для таблиц данных фреймворка).
Рассмотрим пример:

```

$metro = new Metro (array (
'name' => 'м. Авиамоторная'
));
```

Здесь мы создаем модель Metro, и в качестве параметров в конструктор отдаем поля модели, в нашем случае name. Теперь сохраним модель в таблицу базы данных:

```

$metro->save ();
```

После вызова метода save в таблицу ice\_metro будет вставлена новая запись с поле name равным "м. Авиамоторная". Первичный ключ таблицы при этом будет задан автоматически. Если же необходимо явно указать первичный ключ, то следует воспользоваться следующей конструкцией:

```

$metro->id = 3;
$meto->save (true);
```

Если передать в метод save аргумент равный true, то в таблицу ice\_metro будет вставлена новая запись с заданным первичным ключом. Если такая запись уже существовала, то она будет обновлена значениями полей объекта модели. Также здесь мы видим, что к полям модели можно обращаться как к полям объекта metro. Следующий код приведет к тому, что у только что вставленной записи будет изменено поле name.

```

$metro->name = 'м. Автозаводская';
$metro->save ();
```

Также для сохранения модели можно использовать метод update:

```

$metro = new Metro ();
$metro->update (array (
'name'    => 'м. Автозаводская',
'active'  => 1
));
```

Здесь мы передали в метод update помимо поля name еще и поле active. Но что будет если в таблице ice\_metro нету колонки active? На этапе вставки механизм работы с моделями провалидирует входные значения и отбросит несуществующие поля.

##### Model\_Manager #####
Для получения экземпляра конкретной модели можно воспользоваться [[DAO](DAO.md)] фреймворка, которым является [[Model\_Manager](Model_Manager.md)]:
<code php>
$metro = Model\_Manager::byKey (
> 'Metro', 13
);


Unknown end tag for &lt;/code&gt;



Здесь мы пытаемся по первичному ключу получить модель Metro. Для этого используем метод byKey, который в качестве аргументов принимает название модели и значение первичного ключа. Если существует такая модель, то мы получим ее экземпляр, в противном случае получим null.

<code php>
if ($metro)
{
> $metro->update (array (
> > 'name'  => 'м. Автозаводская'

> ));
}


Unknown end tag for &lt;/code&gt;



Если удалось получить модель Metro с первичным ключом 13, то поменяем у нее названием. Почему бы и нет :-)

Чуть-чуть усложним нашу модель данных. Имеем модели Metro\_Line и Metro объявленные ниже:

<code php>
/*** @desc Модель станции метро
  * @var name Назание станции метро
  * @var Metro\_Lineid id линии метро для связи с моделью Metro\_Line
  * 
class Metro extends Model
{
}**

/*** @desc Модель линии метро
  * @var name название линии метро
  * @var color цвет линии метро**/
class Metro\_Line extends Model
{
}



Unknown end tag for &lt;/code&gt;



Имеем модель метро, полученную посредствам [[Model\_Manager](Model_Manager.md)].

<code php>
$metro = Model\_Manager::byKey (
> 'Metro', 13
);


Unknown end tag for &lt;/code&gt;



И хотелось бы узнать к какой линии метро относится данная станция. Можно поступить следующим образом:

<code php>
$metro\_line = Model\_Manager::byKey (
> 'Metro\_Line', $metro->Metro\_Lineid
);


Unknown end tag for &lt;/code&gt;



И мы получим линии метро нашей станции, но вы возможно заметили странность в названии поля Metro\_Lineid. Знаки подчеркивание, не следование правилу cammelCase, что нарушает [[правила именования]] фреймворка, да еще и двойное подчеркивание перед id. Все это сделано не просто так. Используя подобные имена полей мы можем указать, что модель Metro связана с моделью Metro\_Line по полю id модели Metro\_Line. Что и отображено в названии поля: сначала идет название модели, затем поля модели, с которой связанна данная модель. Все это дает нам возможность получать линию метро следующим образом:

<code php>
$metro\_line = $metro->Metro\_Line;


Unknown end tag for &lt;/code&gt;



##### Коллекция моделей #####
Коллекция моделей ([[Model\_Collection](Model_Collection.md)]) представляет собой набор, выбранных по каким-то заданным правилам, моделей. Объявим коллекцию для наших станций метро:

<code php>
/*** @desc Коллекция станций метро
  * 
class Metro\_Collection extends Model\_Collection
{
}


Unknown end tag for &lt;/code&gt;**

Класс коллекции моделей должен наследоваться от класса абстрактной коллекции моделей [[Model\_Collection](Model_Collection.md)].
Создадим экземпляр коллекции моделей Metro:

<code php>
$metro\_collection = new Metro\_Collection ();


Unknown end tag for &lt;/code&gt;



Если создать коллекцию метро таким образом, то туда попадут все находящиеся в таблице ice\_metro записи. Почему так происходит? Как было написано выше, коллекция - это набор моделей, выбранных по определенным правилам. В нашем же случае мы не указываем никаких правил для выборки. Здесь работает правило "что не запрещено, то разрешено" и в коллекцию попадут все возможные модели.

Допустим мы хотим вывести названия всех известных нам станций метро. Чтобы получить модели коллекции следует воспользоваться методом items.

<code php>
$metro\_items = $metro\_collection->items ();
foreach ($metro\_items as $item)
{
> echo $item->name;
}


Unknown end tag for &lt;/code&gt;



Или же можно сделать проще:

<code php>
foreach ($metro\_collection as $item)
{
> echo $item->name;
}


Unknown end tag for &lt;/code&gt;



Здесь мы не получаем модели коллекции методом items, а просто используем ее в качестве аргумента для цикла foreach, куда будет последовательно будут переданы все модели коллекции.
Важно знать, что наполнение коллекции происходит только при первом обращении к ее элементам. При чем коллекция наполняется вся и сразу, одним запросом.

##### Атрибуты модели #####
Атрибуты модели - это внешние поля модели. Пример использования атрибутов:

<code php>
$model = Model\_Manager::byKey ('Metro', 1);
$model->attr ('color', 'red');
echo $model->attr ('color');


Unknown end tag for &lt;/code&gt;



При этом для коллекции будет создано и сохранено внешнее поля. Атрибуты, по умолчанию, сохраняются в таблицу ice\_attribute. Управляет атрибутами [[Attribute\_Manager](Attribute_Manager.md)].

##### Временные данные модели #####
В модели можно хранить временны данные, которые существуют только в момент работы скрипта. Делается это так:

<code php>
$model = Model\_Manager::byKey ('Metro', 2);
$model->data ('color', 'white');
echo $model->data ('color');


Unknown end tag for &lt;/code&gt;



##### Модель-заместитель #####
Иногда необходимо создать модель с данными, которые никогда не могут быть на самом деле. Например: есть какая-то система заказа билетов, с оповещением касс о новом заказе по email. Для мониторинга необходимо, чтобы письмо также приходило на какой-то служебный ящик. Как это сделать? Конечно можно добавить псевдо кассу в таблицу касс с нужными нам данными. Или же можно сделать так:

<code php>
$client\_collection = new Client\_Collection ();
$clinet\_collection->load ();

Loader::load ('Model\_Proxy');
$client\_collection->add (new Model\_Proxy (
> 'Client',
> arrya (
> > 'name'  => 'System',
> > 'email' => 'sys@example.net'

> )
));


Unknown end tag for &lt;/code&gt;



Model\_Proxy реализует паттер [[Proxy](Proxy.md)].

##### Фабрика моделей #####
[[Model\_Factory](Model_Factory.md)] реализует паттерн [[Factory](Abstract.md)]. Давайте начнем с примера:

<code php>
/*** @desc Фабрика типов рассылок. Сами типы рассылок будут храниться в таблице
  * ice\_subscribe. Оттуда мы и будет их получать по имени.
  * 
class Subscribe extends Model\_Factory
{
> > /**
      * @desc Отправляем
      * 
> > public static function subscribe ($name)
> > {
> > > $subscribe = Model\_Manager::byQuery (
> > > > 'Subscribe',
> > > > Query::instance ()
> > > > > ->where ('name', $name)

> > > );


> $user\_collection = new User\_Collection ();
> foreach ($user\_collection as $user)
> {
> > $subscribe->send ($user->email);

> }
> }
}

/*** @desc Абстрактная рассылка
  * 
abstract class Subscribe\_Abstract extends Model\_Factory\_Delegate
{
> > /**
      * @desc Получаем данные для рассылки и отправляем их
      * 
> > abstract public function send ($email);
}

/*** @desc Рассылка новостей
  * 
class Subscribe\_News extends Subscribe\_Abstract
{
> > /**
      * @see Subscribe\_Abstract::send
      * 
> > public function send ($email)
> > {
> > > // Здесь отправляем новости

> > }
}

/*** @desc Рассылка туров
  * 
class Subscribe\_Tour extends Subscribe\_Abstract
{
> > /**
      * @see Subscribe\_Abstract::send
      * 
> > public function send ($email)
> > {
> > > // Здесь отправляем туры

> > }
}

// Начинаем рассылку новостей
Subscribe::subscribe ('News');


Unknown end tag for &lt;/code&gt;



Что у нас получилось? Есть класс Subscribe, который и является фабрикой рассылок. Он наследуется от Model\_Factory.
Subscribe\_Abstract наследуется от Model\_Factory\_Delegate и является абстрактной рассылкой, а Subscribe\_News и Subscribe\_Tour - реализацией этой абстрактной рассылки.
В методе subscribe класса Subscribe мы получаем рассылку по имени. Model\_Factory по полю name находит подходящую реализацию и возвращает ее.