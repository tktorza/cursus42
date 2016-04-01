/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   get_next_line.c                                    :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/01/21 15:31:48 by tktorza           #+#    #+#             */
/*   Updated: 2016/02/01 16:47:07 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "get_next_line.h"

static int	ft_getomi(char *str)
{
	int i;

	i = -1;
	while (str[++i])
		if (str[i] == '\n')
			return (i + 1);
	return (0);
}

static int	ft_gettom(char **b, char **line)
{
	int		eol;
	char	*bprime;

	eol = ft_getomi(*b);
	if (eol > 0)
	{
		*line = ft_strsub(*b, 0, eol - 1);
		bprime = *b;
		*b = ft_strsub(bprime, eol, ft_strlen(bprime + eol));
		free(bprime);
	}
	else
	{
		*line = ft_strdup(*b);
		free(*b);
		*b = ft_strnew(1);
	}
	return (1);
}

int			get_next_line(int fd, char **line)
{
	static char	*b[FD_MAX + 1];
	char		*tmp;
	char		buff[BUFF_SIZE + 1];
	int			ret;

	if (fd < 0 || fd > FD_MAX || line == NULL)
		return (-1);
	if (!b[fd] && !(b[fd] = ft_strnew(1)))
		return (-1);
	ft_bzero(buff, BUFF_SIZE + 1);
	ret = -10;
	while (!ft_getomi(b[fd]) && ((ret = read(fd, buff, BUFF_SIZE)) > 0))
	{
		tmp = b[fd];
		b[fd] = ft_strjoin(tmp, buff);
		free(tmp);
		ft_bzero(buff, BUFF_SIZE);
	}
	if (ret == -1)
		return (-1);
	if (ret == 0 && b[fd][0] == '\0')
		return (0);
	return (ft_gettom(&b[fd], line));
}
