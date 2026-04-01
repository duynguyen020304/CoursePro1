<!-- Parent: ../../AGENTS.md -->
<!-- Generated: 2026-04-01 | Updated: 2026-04-01 -->

# Models

## Purpose
Eloquent ORM models representing database entities. Each model corresponds to a database table and defines relationships, accessors, and business logic. All models use UUID primary keys.

## Key Files
| File | Table | Purpose |
|------|-------|---------|
| `User.php` | users | User accounts with authentication |
| `Role.php` | roles | User roles (admin, student, instructor) |
| `Course.php` | courses | Course catalog |
| `Category.php` | categories | Course categories (hierarchical) |

## Models List

### User & Auth (5 models)
| Model | Table | Key Relationships |
|-------|-------|-------------------|
| `User` | users | belongsTo Role, hasOne Instructor, hasOne Student, hasMany Orders, hasMany Reviews |
| `Role` | roles | hasMany Users |
| `Student` | students | belongsTo User |
| `Instructor` | instructors | belongsTo User, belongsToMany Courses |
| `Review` | reviews | belongsTo User, belongsTo Course |

### Courses & Content (12 models)
| Model | Table | Key Relationships |
|-------|-------|-------------------|
| `Course` | courses | belongsTo User (creator), belongsToMany Instructors, belongsToMany Categories, hasMany Chapters, hasMany Images, hasMany Objectives, hasMany Requirements |
| `Category` | categories | hasMany Children (self-referencing), belongsTo Parent, belongsToMany Courses |
| `CourseChapter` | course_chapters | belongsTo Course, hasMany Lessons |
| `CourseLesson` | course_lessons | belongsTo Course, belongsTo Chapter, hasMany Videos, hasMany Resources |
| `CourseVideo` | course_videos | belongsTo Lesson |
| `CourseResource` | course_resources | belongsTo Lesson |
| `CourseImage` | course_images | belongsTo Course |
| `CourseObjective` | course_objectives | belongsTo Course |
| `CourseRequirement` | course_requirements | belongsTo Course |

### E-commerce (5 models)
| Model | Table | Key Relationships |
|-------|-------|-------------------|
| `Cart` | carts | belongsTo User, hasMany CartItems |
| `CartItem` | cart_items | belongsTo Cart, belongsTo Course |
| `Order` | orders | belongsTo User, belongsTo Course, hasMany Payments |
| `OrderDetail` | order_details | belongsTo Order, belongsTo Course |
| `Payment` | payments | belongsTo Order |

## For AI Agents

### Working In This Directory
- **Primary Keys**: All models use UUID strings (`public $incrementing = false; protected $keyType = 'string';`)
- **Timestamps**: Some models disable timestamps and use manual `boot()` method
- **Relationships**: Define proper inverse relationships
- **Mass Assignment**: Use `$fillable` or `$guarded` for security
- **Table Names**: Explicitly define `$table` property

### Common Patterns
```php
// UUID primary key
class Course extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'courses';

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'price',
        'difficulty',
        'language',
        'created_by',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function chapters()
    {
        return $this->hasMany(CourseChapter::class, 'course_id');
    }
}

// Manual timestamps with boot()
class CourseObjective extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = now();
        });
    }
}
```

## Dependencies

### Internal
- `backend/database/migrations/` - Schema definitions
- `backend/app/Http/Controllers/` - Controllers use models

<!-- MANUAL: Custom models notes can be added below -->
