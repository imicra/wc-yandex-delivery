<?php
/**
 * Class Status.
 * Statuses from Yandex Delivery api.
 */

 namespace Imicra\WcYandexDelivery;

class Status
{
    private const NEW = 'new';
    private const ESTIMATING = 'estimating';
    private const ESTIMATING_FAILED = 'estimating_failed';
    private const READY_FOR_APPROVAL = 'ready_for_approval';
    private const ACCEPTED = 'accepted';
    private const PERFORMER_LOOKUP = 'performer_lookup';
    private const PERFORMER_DRAFT = 'performer_draft';
    private const PERFORMER_FOUND = 'performer_found';
    private const PERFORMER_NOT_FOUND = 'performer_not_found';
    private const PICKUP_ARRIVED = 'pickup_arrived';
    private const READY_FOR_PICKUP_CONFIRMATION = 'ready_for_pickup_confirmation';
    private const PICKUPED = 'pickuped';
    private const PAY_WAITING = 'pay_waiting';
    private const DELIVERY_ARRIVED = 'delivery_arrived';
    private const READY_FOR_DELIVERY_CONFIRMATION = 'ready_for_delivery_confirmation';
    private const DELIVERED = 'delivered';
    private const DELIVERED_FINISH = 'delivered_finish';
    private const RETURNING = 'returning';
    private const RETURN_ARRIVED = 'return_arrived';
    private const READY_FOR_RETURN_CONFIRMATION = 'ready_for_return_confirmation';
    private const RETURNED = 'returned';
    private const RETURNED_FINISH = 'returned_finish';
    private const CANCELLED = 'cancelled';
    private const CANCELLED_WITH_PAYMENT = 'cancelled_with_payment';
    private const CANCELLED_BY_TAXI = 'cancelled_by_taxi';
    private const CANCELLED_WITH_ITEMS_ON_HANDS = 'cancelled_with_items_on_hands';
    private const FAILED = 'failed';

    private const CANCEL_FREE = 'free';
    private const CANCEL_PAID = 'paid';
    private const CANCEL_UNAVAILABLE = 'unavailable';

    /**
     * @return string[]
     */
    public static function namesList(): array
    {
        return [
            self::NEW => 'Новая заявка.',
            self::ESTIMATING => 'Идет процесс оценки заявки',
            self::ESTIMATING_FAILED => 'Не удалось оценить заявку',
            self::READY_FOR_APPROVAL => 'Заявка успешно оценена и ожидает подтверждения от клиента',
            self::ACCEPTED => 'Заявка подтверждена клиентом',
            self::PERFORMER_LOOKUP => 'Заявка взята в обработку',
            self::PERFORMER_DRAFT => 'Идет поиск водителя',
            self::PERFORMER_FOUND => 'Водитель найден',
            self::PERFORMER_NOT_FOUND => 'Не удалось найти водителя',
            self::PICKUP_ARRIVED => 'Водитель приехал в точку А',
            self::READY_FOR_PICKUP_CONFIRMATION => 'Водитель ждет, когда отправитель назовет ему код подтверждения',
            self::PICKUPED => 'Водитель успешно забрал груз',
            self::PAY_WAITING => 'Заказ ожидает оплаты (актуально для оплаты при получении)',
            self::DELIVERY_ARRIVED => 'Водитель приехал в точку Б',
            self::READY_FOR_DELIVERY_CONFIRMATION => 'Водитель ждет, когда получатель назовет ему код подтверждения',
            self::DELIVERED => 'Водитель успешно доставил груз',
            self::DELIVERED_FINISH => 'Заказ завершен',
            self::RETURNING => 'Посылка возвращается на склад',
            self::RETURN_ARRIVED => 'Водитель приехал на точку возврата',
            self::READY_FOR_RETURN_CONFIRMATION => 'Водитель в точке возврата ожидает, когда ему назовут код подтверждения',
            self::RETURNED => 'Водитель успешно вернул груз',
            self::RETURNED_FINISH => 'Заказ завершен',
            self::CANCELLED => 'Заказ был отменен бесплатно',
            self::CANCELLED_WITH_PAYMENT => 'Заказ был отменен платно',
            self::CANCELLED_BY_TAXI => 'Отмена со стороны сервиса',
            self::CANCELLED_WITH_ITEMS_ON_HANDS => 'Отмена (груз остался у водителя)',
            self::FAILED => 'Ошибка, дальнейшее выполнение невозможно',
        ];
    }

    public static function cancelInfo() : array
    {
        return [
            self::CANCEL_FREE => 'Бесплатная',
            self::CANCEL_PAID => 'Платная',
            self::CANCEL_UNAVAILABLE => 'Недоступно',
        ];
    }
}
