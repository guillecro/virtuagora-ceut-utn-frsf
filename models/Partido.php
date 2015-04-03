<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Partido extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;

    //protected $table = 'partidos';
    protected $visible = array('id', 'nombre', 'acronimo', 'descripcion', 'fecha_fundacion', 'fundador');

    public function scopeModifiableBy($query, $id) {
        return $query->whereHas('afiliados', function($q) {
            $q->where('id', $id)->where('es_jefe', 1);
        });
    }

    public function creador() {
        return $this->belongsTo('Usuario');
    }

    public function afiliados() {
        return $this->hasMany('Usuario');
    }

    public function contacto() {
        return $this->morphOne('Contacto', 'contactable');
    }

    public static function boot() {
        parent::boot();
        static::created(function($partido) {
            Usuario::where('id', $partido->creador_id)->update(array('es_jefe' => true,
                                                                     'partido_id' => $partido->id));
        });
        static::deleting(function($partido) {
            Usuario::where('partido_id', $partido->id)->update(array('partido_id' => null,
                                                                     'es_jefe' => false));
            $partido->contacto->delete();
            return true;
        });
    }

}
