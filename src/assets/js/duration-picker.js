/**
 * Duration Picker Plugin
 *
 * @author Takuto Yanagida
 * @version 2022-02-27
 */

((wp) => {
	const {
		date      : { dateI18n },
		data      : { useSelect },
		coreData  : { useEntityProp },
		plugins   : { registerPlugin },
		element   : { useState, Fragment, createElement: el },
		components: { DateTimePicker, Button, PanelRow, Dropdown },
		editPost  : { PluginDocumentSettingPanel },
	} = wp;

	const pmk_from = window?.wpinc_duration_picker?.pmk_from ?? '_date_from';
	const pmk_to   = window?.wpinc_duration_picker?.pmk_to   ?? '_date_to';

	const labels = window?.wpinc_duration_picker?.labels ?? {};
	const format = window?.wpinc_duration_picker?.format ?? 'Y-m-d';

	const DropdownDatePicker = ({ label, defaultLabel, currentDate, onChange, format }) => {
		const [id] = useState(() => _.uniqueId('wpinc-dropdown-date-picker__toggle-'));

		return el(
			Fragment,
			{},
			el('label', { htmlFor: id }, label),
			el(Dropdown, {
				position    : 'bottom left',
				renderToggle: ({ onToggle, isOpen }) => el(
					Fragment,
					{},
					[
						el(
							Button,
							{
								id,
								type        : 'button',
								onClick     : onToggle,
								ariaExpanded: isOpen,
								ariaLive    : 'polite',
								isLink      : true,
							},
							currentDate ? dateI18n(format, currentDate) : defaultLabel
						),
					]
				),
				renderContent: () => el(
					DateTimePicker,
					{
						currentDate,
						onChange: (v) => { onChange(v ? v.split('T')[0] : null); },
					}
				),
			})
		)
	}

	const render = () => {
		const postType        = useSelect((select) => select('core/editor').getCurrentPostType(), []);
		const [meta, setMeta] = useEntityProp('postType', postType, 'meta');
		const updateMeta      = (key, val) => setMeta({ ...meta, [key]: (val ? val : null) });

		return el(
			PluginDocumentSettingPanel, {
				name : 'wpinc-duration-panel',
				title: labels.panel ?? 'Duration',
			},
			el(
				PanelRow,
				{},
				el(
					DropdownDatePicker,
					{
						label       : labels.date_from    ?? 'From',
						defaultLabel: labels.default_from ?? 'Pick Date',
						currentDate : meta[pmk_from],
						onChange    : v => updateMeta(pmk_from, v),
						format,
					}
				)
			),
			el(
				PanelRow,
				{},
				el(
					DropdownDatePicker,
					{
						label       : labels.date_to    ?? 'To',
						defaultLabel: labels.default_to ?? 'Pick Date',
						currentDate : meta[pmk_to],
						onChange    : v => updateMeta(pmk_to, v),
						format,
					}
				)
			),

		);
	};

	registerPlugin('wpinc-duration-picker', { render, icon: 'calendar-alt' });
})(window.wp);
