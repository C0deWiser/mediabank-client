# Mediabank Client

## Использование

Для начала вы должны создать экземпляр сервиса:

```php
use Codewiser\Mediabank\Client\Models\Client as SoapClient;
use Codewiser\Mediabank\Client\MediabankService as MediabankClient;

$url = 'https://example.com/soap.wsdl';

$soap = new SoapClient($url, array("trace" => 1, "exceptions" => 1, 'cache_wsdl' => WSDL_CACHE_NONE));

$login = 'login';
$password = '********';

$soap->Login($login, $password, 'ru');

// Языки, которые поддерживает ваше приложение.
$locales = ['ru', 'en'];

$logger = new \Psr\Log\NullLogger();

$mediabank = new MediabankClient($soap, $locales, $logger);
```

Теперь вы можете обращаться к его методам.

### Обновления

Для получения обновлений потребуется реализация интерфейса `\Codewiser\Mediabank\Client\Contracts\GalleryImporterContract`, о чем будет рассказано далее.

Обновления вы должны производить регулярно, по расписанию (раз в несколько минут).

```php
use Codewiser\Mediabank\Client\MediabankService as MediabankClient;

$mediabank = new MediabankClient($soap, $locales, $logger);

$importer = new GalleryImporter($logger);

$mediabank->fetchUpdates($importer);
```

### Импорт галереи

Вы можете принудительно импортировать одну конкретную галерею:

```php
use Codewiser\Mediabank\Client\MediabankService as MediabankClient;

$mediabank = new MediabankClient($soap, $locales, $logger);

$importer = new GalleryImporter($logger);

$mediabank->fetchGallery($importer, 15647);
```

### Импорт файла

Вы можете принудительно импортировать один конкретный файл:

```php
use Codewiser\Mediabank\Client\MediabankService as MediabankClient;

$mediabank = new MediabankClient($soap, $locales, $logger);

$importer = new GalleryImporter($logger);

$mediabank->fetchMedia($importer, 15647, 645901);
```

### Отправка категорий

В медиа-банке существует возможность отправки галереи сразу в конкретную категорию. Для этого, медиа-банк должен знать, какие категории есть на сайте.

С некоторой периодичностью (раз в день будет достаточно) сайт будет сообщать список своих категорий.

Потребуется реализация интерфейса `\Codewiser\Mediabank\Client\Contracts\CategoryContract`

```php
use Codewiser\Mediabank\Client\MediabankService as MediabankClient;

$mediabank = new MediabankClient($soap, $locales, $logger);

// Достаем из базы категории.
// Оборачиваем каждую в интерфейс CategoryContract.
// Передаем массив в медиа-банк.

$mediabank->pushCategories($categories);
```

## Внедрение

Приложение должно реализовать интерфейсы `\Codewiser\Mediabank\Client\Contracts\MediaImporterContract`, `\Codewiser\Mediabank\Client\Contracts\GalleryImporterContract` и `\Codewiser\Mediabank\Client\Contracts\CategoryContract`.

### GalleryImporterContract

Данный контракт используется для импортирования галереи из медиа-банка в ваше приложение.

```php
use Codewiser\Mediabank\Client\Contracts\GalleryImporterContract;
use Codewiser\Mediabank\Client\Contracts\MediaImporterContract;

class GalleryImporter implements GalleryImporterContract
{
    public function __construct($logger, $filesystem, $database)
    {
        // В конструктор вы можете передать что угодно, что вам нужно для импорта.
        // Например, интерфейс вашего логгера, интерфейс файловой системы,
        // интерфейс базы данных и т.д.   
    }
    
    public function with(int $id): MediaImporterContract
    {
        // Этот метод библиотека использует для перехода к импорту файлов.
      
        // Вы должны вернуть экземпляр MediaImporterContract,
        // которому передадите идентификатор текущей галереи (об этом ниже).
    }
    
    public function shouldImport(int $id): bool
    {
        // Приложение должно сообщить, следует ли импортировать указанную галерею.
        
        // Например, если галерея уже есть в приложении и вы её принудительно
        // сняли с публикации, то тогда можно и не тратиться на повторный импорт.
    }
    
    /**
     * @param \Codewiser\Mediabank\Client\Models\Gallery $info
     */
    public function update(int $id, $info, string $locale): bool
    {
        // Приложение должно создать или обновить галерею.
        
        // В процессе импорта этот метод будет вызван столько раз,
        // сколько в приложении поддерживается языков.
        
        // Объект $info является обычным stdClass,
        // но его свойства соответствуют описанию Models\Gallery
        
        // Если из медиабанка пришла категория ($info->category_id),
        // то не забудьте привязать галерею к этой вашей категории.
        
        // Если галерея выключена ($info->status), то и вы выключайте.
    }
    
    public function cover(int $id, string $url): bool
    {
        // Приложение должно скачать изображение
        // и установить его в качестве обложки для указанной галереи.
         
        // Файл обложки качеством не ахти, поэтому я не рекомендую его использовать.
        // Давайте оставим этот метод пустым.
        
        // Если вашему приложению нужны обложки галерей, лучше взять
        // оригинал соответствующего ($info->cover в методе update()) файла.
    }
    
    public function revoke(int $id): bool
    {
        // Приложение должно убрать указанную галерею из публичного доступа.
        // Лучше — вообще удалить галерею и стереть файлы.
    }
    
    public function route(int $id): ?string
    {
        // Передайте в медиа-банк полный url этой вашей галереи.
        // Если он есть, конечно...
    }
    
    public function finish(int $id): bool
    {
        // Тут приложение может совершить всякие действия,
        // которые нужно совершить после завершения импорта.
        
        // Можно отправить сообщение администратору...
    }
    
    public function files(int $id): array
    {
        // Вы должны вернуть массив идентификаторов
        // существующих у вас файлов из указанной галереи.
        
        // Этот метод используется для вычисления разницы между тем,
        // что в приложении есть, и тем, что в приложении должно быть.
        
        // Если у вас файлов больше, чем надо, то библиотека попросит удалить лишнее.
    }
}
```

### MediaImporterContract

Данный контракт используется для импортирования медиа-файлов из медиа-банка в ваше приложение.

```php
use Codewiser\Mediabank\Client\Contracts\MediaImporterContract;

class MediaImporter implements MediaImporterContract
{
    public function __construct(int $gallery_id, $logger, $filesystem, $database)
    {
        // В конструктор вы можете передать что угодно, что вам нужно для импорта.
        // Например, интерфейс вашего логгера, интерфейс файловой системы,
        // интерфейс базы данных и т.д.
        
        // Этот объект создается в методе GalleryImporterContract::with()
        // и он должен получить от него идентификатор галереи.
        // Все файлы, которые будут импортированы этим классом,
        // относятся к указанной в конструкторе галереи.
        
        // Имейте в виду, что один и тот же файл может присутствовать в разных галереях.
        // Поэтому, например, если медиа-банк просит удалить файл, то вы должны удалить его только 
        // в указанной галерее, а в других оставить.
    }
    
    public function shouldImport(int $id): bool
    {
        // Приложение должно сообщить, следует ли импортировать указанный файл.
        
        // Например, если у вас уже есть этот файл, и с ним всё в порядке,
        // то, наверное, можно и не повторять импорт...
    }
    
    /**
     * @param \Codewiser\Mediabank\Client\Models\Media $info
     */
    public function update(int $id, $info, string $locale): bool
    {
        // Приложение должно создать или обновить медиа.
        
        // В процессе импорта этот метод будет вызван столько раз,
        // сколько в приложении поддерживается языков.
        
        // Объект $info является обычным stdClass,
        // но его свойства соответствуют описанию Models\Media
        
        // Обратите внимание на $info->type (video или photo);
        // от типа файла зависит, что вы будете делать с исходником...
    }
    
    public function cover(int $id, string $url): bool
    {
        // Приложение должно скачать изображение
        // и установить его в качестве обложки для указанного файла.
         
        // Файл обложки качеством не ахти, поэтому я не рекомендую его использовать.
        // Давайте оставим этот метод пустым.
        
        // В качестве обложки лучше использовать «оригинал» файла (см. метод source).
    }
    
    public function source(int $id, string $url): bool
    {
        // Приложение должно скачать и сохранить файл по ссылке,
        // и использовать его в качестве исходника медиа.
        
        // Если медиа — видео, то по ссылке как раз будет его обложка.    
    }
    
    public function zip(int $id, string $url): bool
    {
        // Данный метод будет вызван только для видео-файлов.
        
        // Приложение должно скачать и разархивировать файл по ссылке,
        // и использовать файлы в качестве исходников видео.
    }
    
    public function revoke(int $id): bool
    {
        // Приложение должно убрать указанный файл из публичного доступа.
        // Лучше — вообще удалить файл из базы и диска.
    }
    
    public function route(int $id): ?string
    {
        // Передайте в медиа-банк полный url этого файла.
        // Если он есть, конечно...
    }
    
    public function finish(int $id): bool
    {
        // Тут приложение может совершить всякие действия,
        // которые нужно совершить после завершения импорта.
    }
}
```

