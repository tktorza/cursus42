/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_putwstr.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/05/10 17:44:32 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:06:43 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../../inc/ft_printf.h"

void		pf_ft_putwstr(wchar_t *ws)
{
	size_t	len;

	len = pf_ft_wstrlen(ws);
	while (len > 0)
	{
		pf_ft_putwchar(*ws, f);
		ws++;
		len--;
	}
}
